<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Grpo;
use App\Models\History;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\DailyProduction;
use Illuminate\Http\Request;

class DashboardDailyController extends Controller
{
    public function index()
    {
        // CAPEX card is hidden while there is no CAPEX data at all (no CPX
        // purchase orders from SAP since Oct 2024, no capex budget since Jun 2024).
        // It reappears automatically as soon as CPX PO lines or a capex budget
        // for the current year exist.
        $showCapex = DB::table('powithetas')->where('budget_type', 'CPX')->exists()
            || Budget::where('budget_type_id', 8)->whereYear('date', now()->year)->exists();

        $capex_daily = app(CapexController::class)->capex_daily();
        $reguler_daily = app(CapexController::class)->reguler_daily();
        $grpo_daily = app(GrpoIndexController::class)->index();
        $npi_daily = app(NpiController::class)->index();
        
        // Add daily production data
        $dailyProductionController = new DailyProductionController();
        $dailyProduction = $dailyProductionController->dashboardData(new Request());

        return view('dashboard.daily.index', [
            'report_date' => Carbon::now()->format('d-M-Y'),
            'show_capex' => $showCapex,
            'capex_daily' => $capex_daily,
            'reguler_daily' => $reguler_daily,
            'grpo_daily' => $grpo_daily,
            'npi_daily' => $npi_daily,
            'daily_production' => $dailyProduction
        ]);
    }

    public function getDailyData()
    {
        $capex_daily = app(CapexController::class)->capex_daily();
        $reguler_daily = app(CapexController::class)->reguler_daily();
        $grpo_daily = app(GrpoIndexController::class)->index();
        $npi_daily = app(NpiController::class)->index();
        
        // Add daily production data
        $dailyProductionController = new DailyProductionController();
        $dailyProduction = $dailyProductionController->dashboardData(new Request());

        return [
            'capex_daily' => $capex_daily,
            'reguler_daily' => $reguler_daily,
            'grpo_daily' => $grpo_daily,
            'npi_daily' => $npi_daily,
            'daily_production' => $dailyProduction
        ];
    }
}
