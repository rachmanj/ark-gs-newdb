<?php

namespace App\Http\Controllers;

use App\Models\DailyProduction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DailyProductionsImport;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class DailyProductionController extends Controller
{
    public function index()
    {
        $is_data = DailyProduction::count() > 0 ? 1 : 0;
        return view('daily-production.index', compact('is_data'));
    }

    public function data()
    {
        $query = DailyProduction::query();

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', function ($item) {
                return $item->date ? $item->date->format('d-M-Y') : '';
            })
            ->addColumn('action', function ($item) {
                return '
                    <div class="btn-group" style="gap: 5px;">
                        <a href="' . route('daily-production.show', $item->id) . '" class="btn btn-xs btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="' . route('daily-production.edit', $item->id) . '" class="btn btn-xs btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="' . route('daily-production.destroy', $item->id) . '" method="POST" class="d-inline">
                            ' . method_field('delete') . csrf_field() . '
                            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm(\'Are you sure?\')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function dashboardData()
    {
        // Current month data
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        // Get production data by date and project for current month
        $currentMonthProduction = DailyProduction::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->select(
                'date',
                'project',
                DB::raw('COALESCE(day_shift, 0) + COALESCE(night_shift, 0) as total_production')
            )
            ->orderBy('date')
            ->get();
        
        // Group current month data for the chart
        $currentMonthChartData = [];
        $currentMonthTableData = [];
        
        // Process data for monthly chart and table
        foreach ($currentMonthProduction as $record) {
            $date = $record->date->format('Y-m-d');
            $project = $record->project;
            
            if (!isset($currentMonthChartData[$project])) {
                $currentMonthChartData[$project] = [
                    'name' => $project,
                    'data' => []
                ];
            }
            
            // Add data point for the chart
            $currentMonthChartData[$project]['data'][] = [
                'x' => $date,
                'y' => (float) $record->total_production
            ];
            
            // Build table data
            if (!isset($currentMonthTableData[$project])) {
                $currentMonthTableData[$project] = [
                    'name' => $project,
                    'total' => 0,
                    'days' => []
                ];
            }
            
            $currentMonthTableData[$project]['days'][$date] = (float) $record->total_production;
            $currentMonthTableData[$project]['total'] += (float) $record->total_production;
        }
        
        // Convert to arrays for the view
        $currentMonthChartData = array_values($currentMonthChartData);
        $currentMonthTableData = array_values($currentMonthTableData);
        
        // Get unique dates for the table header
        $currentMonthDates = $currentMonthProduction->pluck('date')
            ->map(function($date) { return $date->format('Y-m-d'); })
            ->unique()
            ->sort()
            ->values()
            ->all();
        
        // Yearly data by month
        $startOfYear = now()->startOfYear();
        $endOfYear = now()->endOfYear();
        
        // Get monthly production data by project for current year
        $yearlyProduction = DailyProduction::whereBetween('date', [$startOfYear, $endOfYear])
            ->select(
                DB::raw('MONTH(date) as month'),
                DB::raw('YEAR(date) as year'),
                'project',
                DB::raw('SUM(COALESCE(day_shift, 0) + COALESCE(night_shift, 0)) as total_production')
            )
            ->groupBy('month', 'year', 'project')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        // Prepare yearly data for chart
        $yearlyChartData = [];
        $yearlyTableData = [];
        $months = [];
        
        // Create month labels
        for ($i = 1; $i <= 12; $i++) {
            $months[] = date('M', mktime(0, 0, 0, $i, 1));
        }
        
        // Process data for yearly chart
        foreach ($yearlyProduction as $record) {
            $monthIdx = $record->month - 1; // 0-based index for months
            $project = $record->project;
            
            if (!isset($yearlyChartData[$project])) {
                $yearlyChartData[$project] = [
                    'name' => $project,
                    'data' => array_fill(0, 12, 0) // Initialize with zeroes for all 12 months
                ];
            }
            
            // Add monthly total to the chart data
            $yearlyChartData[$project]['data'][$monthIdx] = (float) $record->total_production;
            
            // Build yearly table data
            if (!isset($yearlyTableData[$project])) {
                $yearlyTableData[$project] = [
                    'name' => $project,
                    'total' => 0,
                    'months' => array_fill(0, 12, 0)
                ];
            }
            
            $yearlyTableData[$project]['months'][$monthIdx] = (float) $record->total_production;
            $yearlyTableData[$project]['total'] += (float) $record->total_production;
        }
        
        // Convert to arrays for the view
        $yearlyChartData = array_values($yearlyChartData);
        $yearlyTableData = array_values($yearlyTableData);
        
        return [
            'current_month' => [
                'chart_data' => $currentMonthChartData,
                'table_data' => $currentMonthTableData,
                'dates' => $currentMonthDates,
                'month_name' => now()->format('F Y')
            ],
            'yearly' => [
                'chart_data' => $yearlyChartData,
                'table_data' => $yearlyTableData,
                'months' => $months,
                'year' => now()->year
            ]
        ];
    }

    public function create()
    {
        return view('daily-production.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'project' => 'required|string',
            'day_shift' => 'nullable|integer',
            'night_shift' => 'nullable|integer',
            'mtd_ff_actual' => 'nullable|integer',
            'mtd_ff_plan' => 'nullable|integer',
            'mtd_rain_actual' => 'nullable|integer',
            'mtd_rain_plan' => 'nullable|integer',
            'mtd_haul_dist_actual' => 'nullable|integer',
            'mtd_haul_dist_plan' => 'nullable|integer',
            'limestone_day_shift' => 'nullable|integer',
            'limestone_swing_shift' => 'nullable|integer',
            'limestone_night_shift' => 'nullable|integer',
            'shalestone_day_shift' => 'nullable|integer',
            'shalestone_swing_shift' => 'nullable|integer',
            'shalestone_night_shift' => 'nullable|integer',
        ]);

        $data['created_by'] = Auth::id();

        DailyProduction::create($data);

        return redirect()->route('daily-production.index')->with('success', 'Daily production data created successfully!');
    }

    public function show(DailyProduction $dailyProduction)
    {
        return view('daily-production.show', compact('dailyProduction'));
    }

    public function edit(DailyProduction $dailyProduction)
    {
        return view('daily-production.edit', compact('dailyProduction'));
    }

    public function update(Request $request, DailyProduction $dailyProduction)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'project' => 'required|string',
            'day_shift' => 'nullable|integer',
            'night_shift' => 'nullable|integer',
            'mtd_ff_actual' => 'nullable|integer',
            'mtd_ff_plan' => 'nullable|integer',
            'mtd_rain_actual' => 'nullable|integer',
            'mtd_rain_plan' => 'nullable|integer',
            'mtd_haul_dist_actual' => 'nullable|integer',
            'mtd_haul_dist_plan' => 'nullable|integer',
            'limestone_day_shift' => 'nullable|integer',
            'limestone_swing_shift' => 'nullable|integer',
            'limestone_night_shift' => 'nullable|integer',
            'shalestone_day_shift' => 'nullable|integer',
            'shalestone_swing_shift' => 'nullable|integer',
            'shalestone_night_shift' => 'nullable|integer',
        ]);

        $dailyProduction->update($data);

        return redirect()->route('daily-production.index')->with('success', 'Daily production data updated successfully!');
    }

    public function destroy(DailyProduction $dailyProduction)
    {
        $dailyProduction->delete();

        return redirect()->route('daily-production.index')->with('success', 'Daily production data deleted successfully!');
    }

    public function import()
    {
        return view('daily-production.import');
    }

    public function importExcel(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
            ]);

            // Get uploaded file
            $file = $request->file('file');

            // Basic validation
            if (!$file->isValid()) {
                return redirect()->route('daily-production.import')
                    ->with('error', 'Invalid file upload');
            }

            // Handle import with error catching
            try {
                Excel::import(new DailyProductionsImport, $file);
                return redirect()->route('daily-production.index')
                    ->with('success', 'Daily production data imported successfully!');
            } catch (\Exception $e) {
                // Get detailed error message
                $message = $e->getMessage();
                
                // Log the error for debugging
                \Log::error('Excel import failed: ' . $message);
                \Log::error($e->getTraceAsString());
                
                return redirect()->route('daily-production.import')
                    ->with('error', 'Error importing data: ' . $message);
            }
        } catch (\Exception $e) {
            return redirect()->route('daily-production.import')
                ->with('error', 'Error preparing import: ' . $e->getMessage());
        }
    }

    public function exportThisMonth()
    {
        // Implementation for exporting current month data
    }

    public function exportThisYear()
    {
        // Implementation for exporting current year data
    }

    public function truncate()
    {
        try {
            DailyProduction::truncate();
            return redirect()->route('daily-production.index')->with('success', 'All daily production data has been deleted!');
        } catch (\Exception $e) {
            return redirect()->route('daily-production.index')->with('error', 'Failed to delete data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            return Excel::download(new \App\Exports\DailyProductionTemplateExport, 'daily_production_template.xlsx');
        } catch (\Exception $e) {
            return redirect()->route('daily-production.import')->with('error', 'Failed to download template: ' . $e->getMessage());
        }
    }
} 