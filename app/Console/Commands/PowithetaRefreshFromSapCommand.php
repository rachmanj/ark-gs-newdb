<?php

namespace App\Console\Commands;

use App\Http\Controllers\PowithetaController;
use App\Models\Powitheta;
use App\Models\PowithetaSyncHistory;
use App\Services\PowithetaScheduleSettings;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PowithetaRefreshFromSapCommand extends Command
{
    protected $signature = 'powitheta:refresh-from-sap
                            {--scheduled : Use schedule settings for SAP date range (cron)}
                            {--start= : Start date (Y-m-d), overrides schedule custom range when set}
                            {--end= : End date (Y-m-d), overrides schedule custom range when set}';

    protected $description = 'Clear powithetas staging, then run SAP sync (same as UI flow).';

    public function handle(): int
    {
        $scheduled = (bool) $this->option('scheduled');

        $history = PowithetaSyncHistory::create([
            'trigger' => $scheduled ? 'scheduled' : 'cli',
            'user_id' => null,
            'status' => 'running',
            'started_at' => now(),
        ]);

        if ($scheduled) {
            Cache::put('powitheta_scheduled_sync_in_progress', [
                'started_at' => now()->toIso8601String(),
            ], 3600);
        }

        try {
            $this->info('Truncating powithetas (staging only)...');
            try {
                Powitheta::truncate();
            } catch (\Throwable $e) {
                Log::error('powitheta:refresh-from-sap truncate failed', ['exception' => $e->getMessage()]);
                $this->markHistoryFailed($history, $e);
                $this->error($e->getMessage());

                return self::FAILURE;
            }

            $payload = $this->buildDatePayload($scheduled);
            $payload['sync_history_id'] = $history->id;

            $request = Request::create('/powitheta/sync_from_sap', 'POST', $payload);
            $request->headers->set('Accept', 'application/json');
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');

            $this->info('Syncing from SAP...');

            $response = app(PowithetaController::class)->sync_from_sap($request);
            $content = $response->getContent();
            $data = json_decode($content, true);

            if (! is_array($data) || ! array_key_exists('success', $data)) {
                $history->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'message' => 'Unexpected or non-JSON response from sync',
                    'error_detail' => mb_substr((string) $content, 0, 65000),
                ]);
                $this->warn('Unexpected response (non-JSON?). Check SAP connection and logs.');
                Log::warning('powitheta:refresh-from-sap unexpected response', ['content' => $content]);

                return self::FAILURE;
            }

            if ($data['success']) {
                $this->info($data['message'] ?? 'OK');
                Log::info('powitheta:refresh-from-sap completed', ['message' => $data['message'] ?? '']);

                return self::SUCCESS;
            }

            $msg = $data['message'] ?? 'Sync failed';
            $this->error($msg);
            Log::warning('powitheta:refresh-from-sap sync reported failure', ['message' => $msg]);

            return self::FAILURE;
        } catch (\Throwable $e) {
            Log::error('powitheta:refresh-from-sap exception', ['exception' => $e->getMessage()]);
            $this->markHistoryFailed($history, $e);
            $this->error($e->getMessage());

            return self::FAILURE;
        } finally {
            if ($scheduled) {
                Cache::forget('powitheta_scheduled_sync_in_progress');
            }
        }
    }

    private function buildDatePayload(bool $scheduled): array
    {
        if ($scheduled) {
            $payload = PowithetaScheduleSettings::getScheduledSapDatePayload();
            if ($this->option('start')) {
                $payload['start_date'] = $this->option('start');
            }
            if ($this->option('end')) {
                $payload['end_date'] = $this->option('end');
            }

            return array_filter($payload, function ($v) {
                return $v !== null && $v !== '';
            });
        }

        return array_filter([
            'start_date' => $this->option('start') ?: null,
            'end_date' => $this->option('end') ?: null,
        ], function ($v) {
            return $v !== null && $v !== '';
        });
    }

    private function markHistoryFailed(PowithetaSyncHistory $history, \Throwable $e): void
    {
        $history->update([
            'status' => 'failed',
            'finished_at' => now(),
            'message' => $e->getMessage(),
            'error_detail' => mb_substr($e->getTraceAsString(), 0, 65000),
        ]);
    }
}
