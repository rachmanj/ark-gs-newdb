<?php

namespace App\Services;

use App\Http\Controllers\DashboardDailyController;
use App\Models\History;
use Carbon\Carbon;

class MonthlyHistoryCaptureService
{
    /**
     * Snapshots current dashboard daily aggregates into History rows with periode=monthly.
     */
    public function captureFromDailyDashboard(string $captureDate): int
    {
        $general_data = app(DashboardDailyController::class)->getDailyData();

        $batch_no = Carbon::parse($captureDate)->format('Ymd');

        $created = 0;

        foreach ($general_data['capex_daily']['capex'] as $capex) {
            History::create([
                'date' => $captureDate,
                'periode' => 'monthly',
                'gs_type' => 'capex',
                'project_code' => $capex['project'],
                'amount' => $capex['sent_amount'],
                'remarks' => 'BATCH ' . $batch_no,
            ]);
            ++$created;
        }

        foreach ($general_data['reguler_daily']['reguler'] as $reguler) {
            History::create([
                'date' => $captureDate,
                'periode' => 'monthly',
                'gs_type' => 'po_sent',
                'project_code' => $reguler['project'],
                'amount' => $reguler['sent_amount'],
                'remarks' => 'BATCH ' . $batch_no,
            ]);
            ++$created;
        }

        foreach ($general_data['grpo_daily']['grpo_daily'] as $grpo) {
            History::create([
                'date' => $captureDate,
                'periode' => 'monthly',
                'gs_type' => 'grpo_amount',
                'project_code' => $grpo['project'],
                'amount' => $grpo['grpo_amount'],
                'remarks' => 'BATCH ' . $batch_no,
            ]);
            ++$created;
        }

        foreach ($general_data['npi_daily']['npi'] as $incoming) {
            History::create([
                'date' => $captureDate,
                'periode' => 'monthly',
                'gs_type' => 'incoming_qty',
                'project_code' => $incoming['project'],
                'amount' => $incoming['incoming_qty'],
                'remarks' => 'BATCH ' . $batch_no,
            ]);
            ++$created;
        }

        foreach ($general_data['npi_daily']['npi'] as $outgoing) {
            History::create([
                'date' => $captureDate,
                'periode' => 'monthly',
                'gs_type' => 'outgoing_qty',
                'project_code' => $outgoing['project'],
                'amount' => $outgoing['outgoing_qty'],
                'remarks' => 'BATCH ' . $batch_no,
            ]);
            ++$created;
        }

        return $created;
    }
}
