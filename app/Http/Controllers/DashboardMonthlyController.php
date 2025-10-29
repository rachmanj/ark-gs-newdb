<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\History;
use Illuminate\Http\Request;

class DashboardMonthlyController extends Controller
{
    public function index()
    {
        // Get data for the current year and next year for the chart
        $currentYear = date('Y');
        $yearlyData = $this->getYearlyChartData($currentYear);

        return view('dashboard.monthly.index', [
            'yearlyData' => $yearlyData
        ]);
    }

    public function display(Request $request)
    {
        $projects = ['011C', '017C', '021C', '022C', '025C', '026C', 'APS', '023C'];
        $data = app(MonthlyHistoryController::class)->index($request->month);

        // Get data for the current year and next year for the chart
        $year = substr($request->month, 0, 4);
        $yearlyData = $this->getYearlyChartData($year);

        return view('dashboard.monthly.new_display', [
            'month' => $request->month,
            // 'projects' => $projects,
            // 'plant_budget' => $this->plant_budget($request->month),
            // 'histories' => $this->monthly_history_amount($request->month),
            'data' => $data,
            'yearlyData' => $yearlyData
        ]);
    }

    public function plant_budget($date)
    {
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        return Budget::where('budget_type_id', 2)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();
    }

    public function monthly_history_amount($date)
    {
        $month = substr($date, 5, 2);
        $year = substr($date, 0, 4);

        $list = History::where('periode', 'monthly')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        return $list;
    }

    /**
     * Get yearly data for the chart
     * 
     * @param string $year
     * @return array
     */
    public function getYearlyChartData($year)
    {
        $result = [];
        $months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Get current year data
        $currentYearPoSent = [];
        $currentYearGrpo = [];
        $currentYearBudget = [];

        // Get previous year data
        $previousYear = (int)$year - 1;
        $previousYearPoSent = [];
        $previousYearGrpo = [];
        $previousYearBudget = [];

        // Include projects
        $include_projects = ['017C', '021C', '022C', '023C', '025C', '026C', 'APS'];

        // Get data for each month
        foreach ($months as $index => $month) {
            // Current year data
            $currentYearDate = $year . '-' . $month;

            // PO Sent - Sum of all projects for the month
            $poSentAmount = History::where('periode', 'monthly')
                ->whereIn('project_code', $include_projects)
                ->where('gs_type', 'po_sent')
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');
            $currentYearPoSent[] = round($poSentAmount / 1000, 2); // Convert to thousands

            // GRPO Amount - Sum of all projects for the month
            $grpoAmount = History::where('periode', 'monthly')
                ->whereIn('project_code', $include_projects)
                ->where('gs_type', 'grpo_amount')
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');
            $currentYearGrpo[] = round($grpoAmount / 1000, 2); // Convert to thousands

            // Budget - Sum of regular budget for all projects for the month
            $budgetAmount = Budget::where('budget_type_id', 2)
                ->whereIn('project_code', $include_projects)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');
            $currentYearBudget[] = round($budgetAmount / 1000, 2); // Convert to thousands

            // Previous year data
            $previousYearDate = $previousYear . '-' . $month;

            // PO Sent for previous year
            $previousYearPoSentAmount = History::where('periode', 'monthly')
                ->whereIn('project_code', $include_projects)
                ->where('gs_type', 'po_sent')
                ->whereYear('date', $previousYear)
                ->whereMonth('date', $month)
                ->sum('amount');
            $previousYearPoSent[] = round($previousYearPoSentAmount / 1000, 2); // Convert to thousands

            // GRPO Amount for previous year
            $previousYearGrpoAmount = History::where('periode', 'monthly')
                ->whereIn('project_code', $include_projects)
                ->where('gs_type', 'grpo_amount')
                ->whereYear('date', $previousYear)
                ->whereMonth('date', $month)
                ->sum('amount');
            $previousYearGrpo[] = round($previousYearGrpoAmount / 1000, 2); // Convert to thousands

            // Budget for previous year
            $previousYearBudgetAmount = Budget::where('budget_type_id', 2)
                ->whereIn('project_code', $include_projects)
                ->whereYear('date', $previousYear)
                ->whereMonth('date', $month)
                ->sum('amount');
            $previousYearBudget[] = round($previousYearBudgetAmount / 1000, 2); // Convert to thousands
        }

        return [
            'labels' => $monthLabels,
            'currentYear' => $year,
            'previousYear' => $previousYear,
            'datasets' => [
                [
                    'label' => 'PO Sent ' . $year,
                    'data' => $currentYearPoSent,
                    'borderColor' => 'rgba(60, 141, 188, 1)',
                    'backgroundColor' => 'rgba(60, 141, 188, 0.2)',
                    'fill' => true,
                    'tension' => 0.1
                ],
                [
                    'label' => 'Budget ' . $year,
                    'data' => $currentYearBudget,
                    'borderColor' => 'rgba(210, 214, 222, 1)',
                    'backgroundColor' => 'rgba(210, 214, 222, 0.2)',
                    'fill' => true,
                    'tension' => 0.1
                ],
                [
                    'label' => 'GRPO ' . $year,
                    'data' => $currentYearGrpo,
                    'borderColor' => 'rgba(0, 166, 90, 1)',
                    'backgroundColor' => 'rgba(0, 166, 90, 0.2)',
                    'fill' => true,
                    'tension' => 0.1
                ],
                [
                    'label' => 'PO Sent ' . $previousYear,
                    'data' => $previousYearPoSent,
                    'borderColor' => 'rgba(243, 156, 18, 1)',
                    'backgroundColor' => 'rgba(243, 156, 18, 0.05)',
                    'fill' => true,
                    'tension' => 0.1,
                    'borderDash' => [5, 5] // Dashed line for previous year data
                ],
                [
                    'label' => 'GRPO ' . $previousYear,
                    'data' => $previousYearGrpo,
                    'borderColor' => 'rgba(221, 75, 57, 1)',
                    'backgroundColor' => 'rgba(221, 75, 57, 0.05)',
                    'fill' => true,
                    'tension' => 0.1,
                    'borderDash' => [5, 5] // Dashed line for previous year data
                ]
            ]
        ];
    }
}
