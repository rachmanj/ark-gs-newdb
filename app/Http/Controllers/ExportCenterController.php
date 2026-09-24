<?php

namespace App\Http\Controllers;

use App\Exports\ExportCenterWorkbook;
use App\Support\ExportCenterModules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportCenterController extends Controller
{
    public function index()
    {
        $modules = ExportCenterModules::all();

        return view('export-center.index', compact('modules'));
    }

    public function download(Request $request)
    {
        $validated = $request->validate([
            'modules' => 'required|array|min:1',
            'modules.*' => 'in:' . implode(',', ExportCenterModules::codes()),
            'start_month' => 'required|date_format:Y-m',
            'end_month' => 'required|date_format:Y-m|after_or_equal:start_month',
        ]);

        $startDate = Carbon::createFromFormat('Y-m', $validated['start_month'])->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $validated['end_month'])->endOfMonth();

        if ($startDate->diffInMonths($endDate->copy()->startOfMonth()) >= 24) {
            abort(422, 'Rentang maksimum 24 bulan.');
        }

        $filename = sprintf(
            'ARK-GS_export_%s_to_%s.xlsx',
            $validated['start_month'],
            $validated['end_month']
        );

        return Excel::download(
            new ExportCenterWorkbook($validated['modules'], $startDate, $endDate),
            $filename
        );
    }
}
