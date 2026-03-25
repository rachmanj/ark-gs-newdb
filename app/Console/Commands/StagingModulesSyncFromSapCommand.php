<?php

namespace App\Console\Commands;

use App\Http\Controllers\GrpoController;
use App\Http\Controllers\IncomingController;
use App\Http\Controllers\MigiController;
use App\Models\StagingModuleSyncHistory;
use App\Services\PowithetaScheduleSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StagingModulesSyncFromSapCommand extends Command
{
    protected $signature = 'staging-modules:sync-from-sap
                            {--scheduled : Run with Jan 1 → today and dedupe (same clock as POWITHETA schedule)}
                            {--start= : Start date (Y-m-d)}
                            {--end= : End date (Y-m-d)}';

    protected $description = 'GRPO, MIGI, Incoming: SAP sync with duplicate skip (scheduled automatic runs).';

    public function handle(): int
    {
        $scheduled = (bool) $this->option('scheduled');

        if ($scheduled && ! (PowithetaScheduleSettings::get()['staging_modules_enabled'] ?? true)) {
            $this->info('Staging modules scheduled sync is disabled in schedule settings.');

            return self::SUCCESS;
        }

        $runId = (string) Str::uuid();
        $start = $this->option('start')
            ?: Carbon::now()->startOfYear()->format('Y-m-d');
        $end = $this->option('end')
            ?: Carbon::now()->format('Y-m-d');

        if ($scheduled) {
            Cache::put('staging_modules_scheduled_sync_in_progress', [
                'run_id' => $runId,
                'started_at' => now()->toIso8601String(),
                'sap_date_start' => $start,
                'sap_date_end' => $end,
            ], 3600);
        }

        $modules = [
            ['key' => 'grpo', 'controller' => GrpoController::class],
            ['key' => 'migi', 'controller' => MigiController::class],
            ['key' => 'incoming', 'controller' => IncomingController::class],
        ];

        $hadFailure = false;

        try {
            foreach ($modules as $mod) {
                $history = StagingModuleSyncHistory::create([
                    'run_id' => $runId,
                    'module' => $mod['key'],
                    'trigger' => $scheduled ? 'scheduled' : 'cli',
                    'status' => 'running',
                    'started_at' => now(),
                    'sap_date_start' => $start,
                    'sap_date_end' => $end,
                ]);

                try {
                    $request = Request::create('/'.$mod['key'].'/sync_from_sap', 'POST', [
                        'start_date' => $start,
                        'end_date' => $end,
                        'dedupe' => '1',
                    ]);
                    $request->headers->set('Accept', 'application/json');
                    $request->headers->set('X-Requested-With', 'XMLHttpRequest');

                    $response = app($mod['controller'])->sync_from_sap($request);
                    $content = $response->getContent();
                    $data = json_decode($content, true);

                    if (! is_array($data) || ! array_key_exists('success', $data)) {
                        $history->update([
                            'status' => 'failed',
                            'finished_at' => now(),
                            'message' => 'Unexpected or non-JSON response',
                            'error_detail' => mb_substr((string) $content, 0, 65000),
                        ]);
                        $hadFailure = true;
                        $this->warn($mod['key'].': unexpected response');
                        Log::warning('staging-modules: unexpected response', ['module' => $mod['key'], 'content' => $content]);

                        continue;
                    }

                    if ($data['success']) {
                        $history->update([
                            'status' => 'success',
                            'finished_at' => now(),
                            'message' => $data['message'] ?? 'OK',
                            'imported_count' => (int) ($data['imported_count'] ?? 0),
                            'skipped_duplicate_count' => (int) ($data['skipped_duplicate_count'] ?? 0),
                            'total_from_sap' => (int) ($data['total_records'] ?? 0),
                        ]);
                        $this->info(sprintf(
                            '%s: imported %d, skipped dupes %d',
                            $mod['key'],
                            (int) ($data['imported_count'] ?? 0),
                            (int) ($data['skipped_duplicate_count'] ?? 0)
                        ));
                    } else {
                        $history->update([
                            'status' => 'failed',
                            'finished_at' => now(),
                            'message' => $data['message'] ?? 'Sync failed',
                            'error_detail' => isset($data['message']) ? (string) $data['message'] : null,
                        ]);
                        $hadFailure = true;
                        $this->error($mod['key'].': '.($data['message'] ?? 'failed'));
                    }
                } catch (\Throwable $e) {
                    Log::error('staging-modules: module exception', [
                        'module' => $mod['key'],
                        'exception' => $e->getMessage(),
                    ]);
                    $history->update([
                        'status' => 'failed',
                        'finished_at' => now(),
                        'message' => $e->getMessage(),
                        'error_detail' => mb_substr($e->getTraceAsString(), 0, 65000),
                    ]);
                    $hadFailure = true;
                    $this->error($mod['key'].': '.$e->getMessage());
                }
            }
        } finally {
            if ($scheduled) {
                Cache::forget('staging_modules_scheduled_sync_in_progress');
            }
        }

        return $hadFailure ? self::FAILURE : self::SUCCESS;
    }
}
