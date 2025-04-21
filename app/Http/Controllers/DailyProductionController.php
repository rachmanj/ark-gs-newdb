<?php

namespace App\Http\Controllers;

use App\Models\DailyProduction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DailyProductionsImport;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    public function dashboardData(Request $request)
    {
        // Get month and year from request or use current
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Create Carbon instances for first and last day of month
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();

        // Get production data for current month grouped by date and project
        $monthlyProduction = DailyProduction::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        // Get unique projects from the data
        $projects = $monthlyProduction->pluck('project')->unique();

        // Format data for chart
        $chartData = [];
        
        foreach ($projects as $project) {
            $projectData = [
                'name' => $project,
                'data' => []
            ];
            
            $projectRecords = $monthlyProduction->where('project', $project);
            
            foreach ($projectRecords as $record) {
                // Calculate total quantity (day_shift + night_shift)
                $totalQuantity = ($record->day_shift ?? 0) + ($record->night_shift ?? 0);
                
                $projectData['data'][] = [
                    'x' => $record->date->format('Y-m-d'),
                    'y' => $totalQuantity
                ];
            }
            
            $chartData[] = $projectData;
        }

        // Get yearly production totals
        $yearlyData = $this->getYearlyData($year);
        
        // Prepare dates array for table formatting
        $dates = [];
        foreach ($monthlyProduction as $record) {
            $dateStr = $record->date->format('Y-m-d');
            if (!in_array($dateStr, $dates)) {
                $dates[] = $dateStr;
            }
        }
        
        // Prepare table data
        $tableData = [];
        foreach ($projects as $project) {
            $projectTotal = 0;
            $projectDays = [];
            
            $projectRecords = $monthlyProduction->where('project', $project);
            foreach ($projectRecords as $record) {
                $dateStr = $record->date->format('Y-m-d');
                $totalQuantity = ($record->day_shift ?? 0) + ($record->night_shift ?? 0);
                $projectDays[$dateStr] = $totalQuantity;
                $projectTotal += $totalQuantity;
            }
            
            $tableData[] = [
                'name' => $project,
                'total' => $projectTotal,
                'days' => $projectDays
            ];
        }

        // Return response with data in the format expected by the frontend
        $response = [
            'current_month' => [
                'chart_data' => $chartData,
                'table_data' => $tableData,
                'dates' => $dates,
                'selected_month' => (int) $month,
                'selected_year' => (int) $year
            ],
            'yearly' => $yearlyData,
            'selected_month' => (int) $month,
            'selected_year' => (int) $year,
        ];

        return response()->json($response);
    }

    /**
     * Get yearly production data organized by month
     * 
     * @param int $year The year to get data for
     * @return array Monthly production data for the year
     */
    private function getYearlyData($year)
    {
        // Create date ranges for the year
        $startOfYear = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $endOfYear = Carbon::createFromDate($year, 12, 31)->endOfDay();

        // Get production data grouped by month and project
        $yearlyProduction = DailyProduction::whereBetween('date', [$startOfYear, $endOfYear])
            ->select(
                DB::raw('MONTH(date) as month'),
                'project',
                DB::raw('SUM(day_shift + night_shift) as total_quantity')
            )
            ->groupBy('month', 'project')
            ->orderBy('month')
            ->get();

        // Get unique projects
        $projects = $yearlyProduction->pluck('project')->unique();
        
        // Prepare month names for labels
        $monthNames = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthNames[] = date('M', mktime(0, 0, 0, $i, 1));
        }
        
        // Organize data by project for chart
        $chartData = [];
        // Organize data for table
        $tableData = [];
        
        foreach ($projects as $project) {
            $projectData = [
                'name' => $project,
                'data' => array_fill(0, 12, 0), // Initialize with zeroes for all 12 months
            ];
            
            // For table data - initialize with zeroes for all 12 months
            $projectMonths = array_values(array_fill(0, 12, 0)); // Make sure it's a zero-indexed array
            $projectTotal = 0;
            
            // Fill in actual data for months that have records
            $projectRecords = $yearlyProduction->where('project', $project);
            
            foreach ($projectRecords as $record) {
                $monthIndex = $record->month - 1; // Convert from 1-based to 0-based indexing for chart
                $projectData['data'][$monthIndex] = (float) $record->total_quantity;
                
                // For table data (also using 0-based month indexing)
                $projectMonths[$monthIndex] = (float) $record->total_quantity;
                $projectTotal += (float) $record->total_quantity;
            }
            
            $chartData[] = $projectData;
            
            // Add to table data
            $tableData[] = [
                'name' => $project,
                'total' => $projectTotal,
                'months' => $projectMonths // Now a zero-indexed array
            ];
        }
        
        return [
            'chart_data' => $chartData,
            'table_data' => $tableData,
            'months' => $monthNames,
            'year' => $year
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