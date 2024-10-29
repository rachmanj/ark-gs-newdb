<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Powitheta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public $include_projects = ['017C', '021C', '022C', '023C', 'APS'];

    public function index()
    {
        $test = app(CapexController::class)->reguler_daily();
        // $test = app(YearlyIndexController::class)->index();
        // $test = app(YearlyIndexController::class)->periode();
        // $test = app(YearlyHistoryController::class)->index('2021-01-01');
        // $test = app(MonthlyHistoryController::class)->index('2024-01-31');
        // $test = app(BudgetController::class)->getPlantBudgetOfMonth('2024-01-01');
        // $test = app(DashboardDailyController::class)->getDailyData();
        // $test = app(PowithetaController::class)->compare_db();
        // $test = app(PowithetaController::class)->clean_olddb();

        return $test;
    }
}
