<?php

namespace App\Console\Commands;

use App\Http\Controllers\IncomingController;
use App\Http\Controllers\MigiController;
use App\Http\Controllers\PowithetaController;
use App\Models\Incoming;
use App\Models\Migi;
use App\Models\Powitheta;
use App\Services\SapService;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VerifySapSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap:verify-sync
                            {module=all : incoming|migi|powitheta|all}
                            {--start= : Override start date (Y-m-d)}
                            {--end= : Override end date (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare SAP SQL Server results with local tables to verify sync accuracy';

    public function handle(SapService $sapService): int
    {
        $modules = [
            'incoming' => [
                'controller' => IncomingController::class,
                'model' => Incoming::class,
                'serviceMethod' => 'executeIncomingSqlQuery',
                'dateColumn' => 'posting_date',
                'defaultStart' => Carbon::now()->startOfYear()->toDateString(),
                'defaultEnd' => Carbon::now()->toDateString(),
                'keyColumns' => ['doc_no', 'item_code', 'project_code', 'posting_date'],
            ],
            'migi' => [
                'controller' => MigiController::class,
                'model' => Migi::class,
                'serviceMethod' => 'executeMigiSqlQuery',
                'dateColumn' => 'posting_date',
                'defaultStart' => Carbon::now()->startOfYear()->toDateString(),
                'defaultEnd' => Carbon::now()->toDateString(),
                'keyColumns' => ['doc_no', 'item_code', 'project_code', 'posting_date'],
            ],
            'powitheta' => [
                'controller' => PowithetaController::class,
                'model' => Powitheta::class,
                'serviceMethod' => 'executePowithetaSqlQuery',
                'dateColumn' => 'posting_date',
                'defaultStart' => '2024-12-01',
                'defaultEnd' => Carbon::now()->toDateString(),
                'keyColumns' => ['po_no', 'item_code', 'project_code'],
            ],
        ];

        $selected = $this->argument('module');
        if ($selected !== 'all' && !isset($modules[$selected])) {
            $this->error("Unknown module: {$selected}");
            return self::FAILURE;
        }

        $targets = $selected === 'all' ? array_keys($modules) : [$selected];

        foreach ($targets as $module) {
            $config = $modules[$module];
            $startDate = $this->option('start') ?? $config['defaultStart'];
            $endDate = $this->option('end') ?? $config['defaultEnd'];

            $this->line(str_repeat('-', 80));
            $this->info(Str::upper($module) . " | Range: {$startDate} → {$endDate}");

            // Fetch fresh data from SAP
            $sapData = $sapService->{$config['serviceMethod']}($startDate, $endDate);
            $sapCount = count($sapData);
            $this->comment("SAP rows: {$sapCount}");

            // Truncate model table before syncing
            $config['model']::truncate();

            // Trigger controller sync via fake request (JSON)
            /** @var \Illuminate\Http\Request $request */
            $request = Request::create("/{$module}/sync_from_sap", 'POST', [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
            $request->headers->set('Accept', 'application/json');
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');

            /** @var \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse $response */
            $response = app($config['controller'])->sync_from_sap($request);

            $payload = method_exists($response, 'getContent')
                ? json_decode($response->getContent(), true)
                : null;

            if ($payload && isset($payload['message'])) {
                $this->info("Controller response: {$payload['message']}");
            } else {
                $this->warn('Controller response not JSON; sync likely redirected.');
            }

            // Fetch local data for comparison
            /** @var \Illuminate\Database\Eloquent\Collection $localData */
            $localData = $config['model']::query()
                ->whereBetween($config['dateColumn'], [$startDate, $endDate])
                ->where('batch', 1)
                ->get();

            $localCount = $localData->count();
            $this->comment("Local rows: {$localCount}");

            // Compare datasets
            $sapKeys = $this->buildKeyCollection(collect($sapData), $config['keyColumns']);
            $localKeys = $this->buildKeyCollection($localData, $config['keyColumns']);

            $missingInLocal = $sapKeys->diff($localKeys);
            $extraInLocal = $localKeys->diff($sapKeys);

            if ($missingInLocal->isEmpty() && $extraInLocal->isEmpty()) {
                $this->info("✅ {$module}: Data matches between SAP and local table.");
            } else {
                $this->error("❌ {$module}: Detected mismatches.");
                if ($missingInLocal->isNotEmpty()) {
                    $this->warn('  Present in SAP but missing locally (sample):');
                    $this->line('  - ' . $missingInLocal->take(5)->implode("\n  - "));
                }
                if ($extraInLocal->isNotEmpty()) {
                    $this->warn('  Present locally but not in SAP (sample):');
                    $this->line('  - ' . $extraInLocal->take(5)->implode("\n  - "));
                }
            }
        }

        $this->line(str_repeat('-', 80));
        $this->info('Verification finished.');

        return self::SUCCESS;
    }

    /**
     * Build comparable keys for a dataset.
     */
    private function buildKeyCollection(Collection $rows, array $columns): Collection
    {
        return $rows->map(function ($row) use ($columns) {
            if ($row instanceof Model) {
                $payload = $row->getAttributes();
            } else {
                $payload = (array) $row;
            }

            $parts = [];
            foreach ($columns as $column) {
                $value = $payload[$column] ?? '';

                if ($value instanceof DateTimeInterface) {
                    $value = Carbon::instance($value)->toDateString();
                } elseif (!empty($value) && Str::contains($column, 'date')) {
                    try {
                        $value = Carbon::parse($value)->toDateString();
                    } catch (\Exception $e) {
                        // Leave original value if parsing fails
                    }
                }

                $parts[] = (string) $value;
            }

            return implode('|', $parts);
        })->sort()->values();
    }
}

