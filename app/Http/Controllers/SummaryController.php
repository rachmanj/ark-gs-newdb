<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Powitheta;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\SummaryExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class SummaryController extends Controller
{
    public function index()
    {
        $unitSummary = $this->getUnitSummary();
        return view('dashboard.summary-by-unit.index', compact('unitSummary'));
    }

    public function getUnitSummary()
    {
        $incl_deptcode = ['40', '50', '60', '140', '200'];
        $excl_itemcode = ['EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%'];

        // Get the current year
        $year = date('Y');

        $summary = Powitheta::select(
            'unit_no',
            DB::raw('MONTH(po_delivery_date) as month'),
            DB::raw('FORMAT(SUM(item_amount)/1000, 2) as total_amount'),
            DB::raw('COUNT(DISTINCT po_no) as po_count')
        )
            ->whereNotNull('unit_no')
            ->whereNotNull('po_delivery_date')
            ->whereYear('po_delivery_date', $year)
            ->whereIn('dept_code', $incl_deptcode)
            ->where('po_status', '!=', 'Cancelled')
            ->where('po_delivery_status', 'Delivered')
            ->where(function ($query) use ($excl_itemcode) {
                foreach ($excl_itemcode as $pattern) {
                    $query->where('item_code', 'not like', $pattern);
                }
            })
            ->groupBy('unit_no', 'month')
            ->orderBy('unit_no', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Get yearly totals for each unit
        $yearlyTotals = Powitheta::select(
            'unit_no',
            DB::raw('FORMAT(SUM(item_amount)/1000, 2) as yearly_total'),
            DB::raw('COUNT(DISTINCT po_no) as total_po_count')
        )
            ->whereNotNull('unit_no')
            ->whereNotNull('po_delivery_date')
            ->whereYear('po_delivery_date', $year)
            ->whereIn('dept_code', $incl_deptcode)
            ->where('po_status', '!=', 'Cancelled')
            ->where('po_delivery_status', 'Delivered')
            ->where(function ($query) use ($excl_itemcode) {
                foreach ($excl_itemcode as $pattern) {
                    $query->where('item_code', 'not like', $pattern);
                }
            })
            ->groupBy('unit_no')
            ->orderBy('unit_no', 'asc')
            ->get();

        // Convert yearly totals to associative array
        $yearlyTotalsArray = [];
        foreach ($yearlyTotals as $total) {
            $yearlyTotalsArray[$total->unit_no] = [
                'yearly_total' => $total->yearly_total,
                'total_po_count' => $total->total_po_count
            ];
        }

        // Create array with all months
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthName = date('M', mktime(0, 0, 0, $m, 1));
            $months[$m] = [
                'month_name' => $monthName,
                'units' => []
            ];
        }

        // Group data by months
        foreach ($summary as $record) {
            $month = (int)$record->month;
            $months[$month]['units'][] = [
                'unit_no' => $record->unit_no,
                'total_amount' => $record->total_amount,
                'po_count' => $record->po_count
            ];
        }

        // Convert to array and ensure all months are included
        $result = array_values(array_map(function ($month) {
            return [
                'month' => $month['month_name'],
                'units' => $month['units']
            ];
        }, $months));

        return [
            'months' => $result,
            'yearly_totals' => $yearlyTotalsArray
        ];
    }

    public function showSummaryTable()
    {
        $summaryData = $this->getUnitSummary();

        // Get unique unit numbers across all months
        $unitNumbers = collect();
        foreach ($summaryData['months'] as $month) {
            foreach ($month['units'] as $unit) {
                $unitNumbers->push($unit['unit_no']);
            }
        }
        $unitNumbers = $unitNumbers->unique()->sort()->values();

        return view('summary.table', [
            'months' => $summaryData['months'],
            'unitNumbers' => $unitNumbers,
            'yearly_totals' => $summaryData['yearly_totals']
        ]);
    }

    public function exportExcel()
    {
        $summaryData = $this->getUnitSummary();

        // Get unique unit numbers across all months
        $unitNumbers = collect();
        foreach ($summaryData['months'] as $month) {
            foreach ($month['units'] as $unit) {
                $unitNumbers->push($unit['unit_no']);
            }
        }
        $unitNumbers = $unitNumbers->unique()->sort()->values();

        $fileName = 'summary_report_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(
            new SummaryExport(
                $summaryData['months'], 
                $unitNumbers, 
                $summaryData['yearly_totals']
            ), 
            $fileName
        );
    }

    public function exportPdf()
    {
        $summaryData = $this->getUnitSummary();

        // Get unique unit numbers across all months
        $unitNumbers = collect();
        foreach ($summaryData['months'] as $month) {
            foreach ($month['units'] as $unit) {
                $unitNumbers->push($unit['unit_no']);
            }
        }
        $unitNumbers = $unitNumbers->unique()->sort()->values();

        $pdf = PDF::loadView('exports.summary_pdf', [
            'months' => $summaryData['months'],
            'unitNumbers' => $unitNumbers,
            'yearly_totals' => $summaryData['yearly_totals']
        ])->setPaper('a4', 'landscape');

        return $pdf->download('summary_report_' . date('Y-m-d') . '.pdf');
    }
}
