<?php

namespace App\Console\Commands;

use App\Services\MonthlyHistoryCaptureService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class GenerateMonthlyHistoriesCommand extends Command
{
    protected $signature = 'history:generate-monthly
                            {capture_date? : Date stored on new rows (Y-m-d); defaults to today}';

    protected $description = 'Snapshot dashboard daily KPIs into monthly History rows (same as Generate Monthly Histories modal)';

    public function handle(MonthlyHistoryCaptureService $monthlyHistoryCapture): int
    {
        $raw = $this->argument('capture_date') ?: now()->toDateString();

        $validator = Validator::make(['capture_date' => $raw], [
            'capture_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $captureDate = $validator->validated()['capture_date'];

        try {
            $count = $monthlyHistoryCapture->captureFromDailyDashboard($captureDate);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Created {$count} history row(s) for capture date {$captureDate}.");

        return self::SUCCESS;
    }
}
