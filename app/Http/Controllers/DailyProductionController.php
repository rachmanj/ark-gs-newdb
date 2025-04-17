<?php

namespace App\Http\Controllers;

use App\Models\DailyProduction;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DailyProductionsImport;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

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