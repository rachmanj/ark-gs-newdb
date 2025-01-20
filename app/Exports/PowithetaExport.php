<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PowithetaExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            '#',
            'po_no',
            'create_date',
            'posting_date',
            'vendor_code',
            'item_code',
            'description',
            'uom',
            'qty',
            'unit_no',
            'project_code',
            'dept_code',
            'po_currency',
            'unit_price',
            'item_amount',
            'total_po_price',
            'po_with_vat',
            'po_status',
            'po_delivery_status',
            'po_delivery_date',
            'po_eta',
            'remarks',
            'budget_type',
            'created_at',
            'updated_at',
        ];
    }

    public function collection()
    {
        $date = Carbon::now();
        $projects = [
            '017C',
            '021C',
            '022C',
            '023C',
            'APS'
        ];

        return $this->po_sent_amount($date, $projects);
    }

    public function po_sent_amount($date, $project)
    {
        $incl_deptcode = ['40', '50', '60', '140', '200'];

        $excl_itemcode = ['EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%']; // , 
        foreach ($excl_itemcode as $e) {
            $excl_itemcode_arr[] = ['item_code', 'not like', $e];
        };

        $list = DB::table('powithetas')
            ->whereIn('dept_code', $incl_deptcode)
            ->where($excl_itemcode_arr)
            ->whereYear('po_delivery_date', $date)
            ->whereMonth('po_delivery_date', $date)
            ->whereIn('project_code', $project)
            ->where('po_status', '!=', 'Cancelled')
            ->where('po_delivery_status', '=', 'Delivered');
        // ->distinct('po_no');

        return $list->get();
    }
}
