<?php

namespace App\Http\Controllers;

use App\Exports\ExportCenterWorkbook;
use App\Repositories\SapQueryRepository;
use App\Support\ExportCenterModules;
use App\Support\SapReportModules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportCenterController extends Controller
{
    public function index()
    {
        $arkModules = ExportCenterModules::all();
        $sapModules = SapReportModules::all();

        return view('export-center.index', compact('arkModules', 'sapModules'));
    }

    public function download(Request $request, SapQueryRepository $sapQueryRepository)
    {
        $allowedCodes = array_merge(ExportCenterModules::codes(), SapReportModules::codes());

        $validated = $request->validate([
            'modules' => 'required|array|min:1',
            'modules.*' => 'in:' . implode(',', $allowedCodes),
            'start_month' => 'required|date_format:Y-m',
            'end_month' => 'required|date_format:Y-m|after_or_equal:start_month',
        ]);

        set_time_limit(300);

        $startDate = Carbon::createFromFormat('Y-m', $validated['start_month'])->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $validated['end_month'])->endOfMonth();
        $fromDate = $startDate->format('Y-m-d');
        $toDate = $endDate->format('Y-m-d');

        if ($startDate->diffInMonths($endDate->copy()->startOfMonth()) >= 24) {
            abort(422, 'Rentang maksimum 24 bulan.');
        }

        $maxRows = 150000;
        $totalRows = 0;
        $sapRowData = [];

        foreach ($validated['modules'] as $moduleCode) {
            $sapModule = SapReportModules::get($moduleCode);
            if ($sapModule !== null) {
                $rows = $sapQueryRepository->run(
                    $sapModule['ouqr_key'],
                    $sapModule['uses_date_range'] ? $fromDate : null,
                    $sapModule['uses_date_range'] ? $toDate : null
                );
                $sapRowData[$moduleCode] = $rows;
                $totalRows += count($rows);

                continue;
            }

            $module = ExportCenterModules::get($moduleCode);
            $model = $module['model'];
            $dateColumn = $module['date_column'];

            $totalRows += $model::query()
                ->whereBetween($dateColumn, [$startDate, $endDate])
                ->count();
        }

        if ($totalRows > $maxRows) {
            abort(422, "Rentang terlalu besar. Pilih lebih sedikit modul atau rentang bulan yang lebih pendek. Total baris: {$totalRows}, batas: {$maxRows}.");
        }

        ini_set('memory_limit', '2048M');

        $filename = sprintf(
            'ARK-GS_export_%s_to_%s.xlsx',
            $validated['start_month'],
            $validated['end_month']
        );

        return Excel::download(
            new ExportCenterWorkbook($validated['modules'], $startDate, $endDate, $sapRowData),
            $filename
        );
    }
}
