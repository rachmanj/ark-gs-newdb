<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardYearlyController extends Controller
{
    public function index()
    {
        $years = DB::table('histories')->select('periode', 'date')
            ->where('periode', 'yearly')
            ->whereYear('date', '<', Carbon::now())
            ->distinct('date')
            ->orderBy('date', 'desc')
            ->get();

        $multiYearData = $this->getMultiYearChartData();
        $currentYearStats = $this->getCurrentYearStats();
        $data = $this->getCurrentYearPreviewData();

        return view('dashboard.yearly.index', compact('years', 'multiYearData', 'currentYearStats', 'data'));
    }

    public function display(Request $request)
    {
        $this->validate($request, [
            'year' => ['required']
        ]);

        $years = DB::table('histories')->select('periode', 'date')
            ->where('periode', 'yearly')
            ->whereYear('date', '<', Carbon::now())
            ->distinct('date')
            ->orderBy('date', 'desc')
            ->get();

        if ($request->year !== 'this_year') {
            $year_title = $request->year;

            $data = app(YearlyHIstoryController::class)->index($request->year);

            return view('dashboard.yearly.new_display', [
                'year_title' => $year_title,
                'years' => $years,
                'data' => $data,
            ]);
        } else {
            $data = app(YearlyIndexController::class)->index();

            return view('dashboard.yearly.new_display', [
                'year_title' => 'This Year',
                'years' => $years,
                'data' => $data,
            ]);
        }
    }

    public function export(Request $request)
    {
        $this->validate($request, [
            'year' => ['required'],
            'format' => ['required', 'in:excel,pdf,csv']
        ]);

        $year_title = $request->year === 'this_year' ? 'This Year' : $request->year;

        if ($request->year !== 'this_year') {
            $data = app(YearlyHIstoryController::class)->index($request->year);
        } else {
            $data = app(YearlyIndexController::class)->index();
        }

        $filename = 'yearly_dashboard_' . ($request->year === 'this_year' ? date('Y') : date('Y', strtotime($request->year))) . '_' . date('Y-m-d');

        switch ($request->format) {
            case 'excel':
                return $this->exportExcel($data, $year_title, $filename);
            case 'pdf':
                return $this->exportPDF($data, $year_title, $filename);
            case 'csv':
                return $this->exportCSV($data, $year_title, $filename);
        }
    }

    private function exportExcel($data, $year_title, $filename)
    {
        $excel = \App::make('excel');

        return $excel->create($filename, function ($excel) use ($data, $year_title) {
            $excel->setTitle('Yearly Dashboard - ' . $year_title);
            $excel->setCreator('ARK-GS System');
            $excel->setCompany('ARKA');

            // Regular Budget Sheet
            $excel->sheet('Regular Budget', function ($sheet) use ($data) {
                $sheet->loadView('dashboard.yearly.exports.reguler', compact('data'));
            });

            // CAPEX Sheet
            $excel->sheet('CAPEX', function ($sheet) use ($data) {
                $sheet->loadView('dashboard.yearly.exports.capex', compact('data'));
            });

            // GRPO Sheet
            $excel->sheet('GRPO', function ($sheet) use ($data) {
                $sheet->loadView('dashboard.yearly.exports.grpo', compact('data'));
            });

            // NPI Sheet
            $excel->sheet('NPI', function ($sheet) use ($data) {
                $sheet->loadView('dashboard.yearly.exports.npi', compact('data'));
            });
        })->download('xlsx');
    }

    private function exportPDF($data, $year_title, $filename)
    {
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadView('dashboard.yearly.exports.pdf', compact('data', 'year_title'));
        return $pdf->download($filename . '.pdf');
    }

    private function exportCSV($data, $year_title, $filename)
    {
        $callback = function () use ($data, $year_title) {
            $file = fopen('php://output', 'w');

            // CSV Header
            fputcsv($file, ['ARK-GS Yearly Dashboard - ' . $year_title]);
            fputcsv($file, ['Generated on: ' . date('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Regular Budget Data
            fputcsv($file, ['REGULAR BUDGET']);
            fputcsv($file, ['Project', 'PO Sent (IDR)', 'Budget (IDR)', 'Percentage (%)']);
            foreach ($data['reguler']['reguler_yearly'] as $item) {
                fputcsv($file, [
                    $item['project'],
                    number_format($item['sent_amount'], 2),
                    number_format($item['budget'], 2),
                    number_format($item['percentage'] * 100, 2)
                ]);
            }
            fputcsv($file, [
                'TOTAL',
                number_format($data['reguler']['sent_total'], 2),
                number_format($data['reguler']['budget_total'], 2),
                number_format($data['reguler']['percentage'] * 100, 2)
            ]);
            fputcsv($file, []);

            // CAPEX Data
            fputcsv($file, ['CAPEX']);
            fputcsv($file, ['Project', 'PO Sent (IDR)', 'Budget (IDR)', 'Percentage (%)']);
            foreach ($data['capex']['capex'] as $item) {
                fputcsv($file, [
                    $item['project'],
                    number_format($item['sent_amount'], 2),
                    number_format($item['budget'], 2),
                    number_format($item['percentage'] * 100, 2)
                ]);
            }
            fputcsv($file, [
                'TOTAL',
                number_format($data['capex']['sent_total'], 2),
                number_format($data['capex']['budget_total'], 2),
                number_format($data['capex']['percentage'] * 100, 2)
            ]);
            fputcsv($file, []);

            // GRPO Data
            fputcsv($file, ['GRPO']);
            fputcsv($file, ['Project', 'PO Sent (IDR)', 'GRPO (IDR)', 'Percentage (%)']);
            foreach ($data['grpo']['grpo_yearly'] as $item) {
                fputcsv($file, [
                    $item['project'],
                    number_format($item['po_sent_amount'], 2),
                    number_format($item['grpo_amount'], 2),
                    number_format($item['percentage'] * 100, 2)
                ]);
            }
            fputcsv($file, [
                'TOTAL',
                number_format($data['grpo']['total_po_sent_amount'], 2),
                number_format($data['grpo']['total_grpo_amount'], 2),
                number_format($data['grpo']['total_percentage'] * 100, 2)
            ]);
            fputcsv($file, []);

            // NPI Data
            fputcsv($file, ['NPI']);
            fputcsv($file, ['Project', 'In (Units)', 'Out (Units)', 'Index']);
            foreach ($data['npi']['npi'] as $item) {
                fputcsv($file, [
                    $item['project'],
                    number_format($item['incoming_qty'], 0),
                    number_format($item['outgoing_qty'], 0),
                    number_format($item['percentage'], 2)
                ]);
            }
            fputcsv($file, [
                'TOTAL',
                number_format($data['npi']['total_incoming_qty'], 0),
                number_format($data['npi']['total_outgoing_qty'], 0),
                number_format($data['npi']['total_percentage'], 2)
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }

    private function getMultiYearChartData()
    {
        $currentYear = Carbon::now()->year;
        $years = [];
        $budgetData = [];
        $poSentData = [];
        $grpoData = [];
        
        $include_projects = ['017C', '021C', '022C', '025C', '026C', 'APS', '023C'];

        for ($i = 4; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $years[] = $year;

            $budget = DB::table('budgets')
                ->whereIn('project_code', $include_projects)
                ->where('budget_type_id', 2)
                ->whereYear('date', $year)
                ->sum('amount');
            $budgetData[] = round($budget / 1000, 2);

            $poSent = DB::table('histories')
                ->whereIn('project_code', $include_projects)
                ->where('periode', 'yearly')
                ->where('gs_type', 'po_sent')
                ->whereYear('date', $year)
                ->sum('amount');
            $poSentData[] = round($poSent / 1000, 2);

            $grpo = DB::table('histories')
                ->whereIn('project_code', $include_projects)
                ->where('periode', 'yearly')
                ->where('gs_type', 'grpo_amount')
                ->whereYear('date', $year)
                ->sum('amount');
            $grpoData[] = round($grpo / 1000, 2);
        }

        return [
            'labels' => $years,
            'datasets' => [
                [
                    'label' => 'Budget',
                    'data' => $budgetData,
                    'borderColor' => 'rgba(210, 214, 222, 1)',
                    'backgroundColor' => 'rgba(210, 214, 222, 0.2)',
                    'fill' => true,
                    'tension' => 0.4
                ],
                [
                    'label' => 'PO Sent',
                    'data' => $poSentData,
                    'borderColor' => 'rgba(60, 141, 188, 1)',
                    'backgroundColor' => 'rgba(60, 141, 188, 0.2)',
                    'fill' => true,
                    'tension' => 0.4
                ],
                [
                    'label' => 'GRPO',
                    'data' => $grpoData,
                    'borderColor' => 'rgba(0, 166, 90, 1)',
                    'backgroundColor' => 'rgba(0, 166, 90, 0.2)',
                    'fill' => true,
                    'tension' => 0.4
                ]
            ]
        ];
    }

    private function getCurrentYearStats()
    {
        $currentYear = Carbon::now()->year;
        $include_projects = ['017C', '021C', '022C', '025C', '026C', 'APS', '023C'];

        $totalBudget = DB::table('budgets')
            ->whereIn('project_code', $include_projects)
            ->where('budget_type_id', 2)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $totalPoSent = DB::table('histories')
            ->whereIn('project_code', $include_projects)
            ->where('periode', 'yearly')
            ->where('gs_type', 'po_sent')
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $totalGrpo = DB::table('histories')
            ->whereIn('project_code', $include_projects)
            ->where('periode', 'yearly')
            ->where('gs_type', 'grpo_amount')
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $budgetPerformance = $totalBudget > 0 ? ($totalPoSent / $totalBudget) * 100 : 0;

        $npiData = app(YearlyIndexController::class)->index();
        $productionIndex = $npiData['npi']['total_percentage'] ?? 0;

        return [
            'totalBudget' => $totalBudget,
            'totalPoSent' => $totalPoSent,
            'totalGrpo' => $totalGrpo,
            'budgetPerformance' => $budgetPerformance,
            'productionIndex' => $productionIndex
        ];
    }

    private function getCurrentYearPreviewData()
    {
        $currentYearData = app(YearlyIndexController::class)->index();
        
        return [
            'reguler' => $currentYearData['reguler'],
            'capex' => $currentYearData['capex'],
            'grpo' => $currentYearData['grpo'],
            'npi' => $currentYearData['npi']
        ];
    }
}
