<?php

namespace App\Exports;

use App\Models\Grpo;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GrpoExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            '#',
            'po_no',
            'po_date',
            'po_delivery_date',
            'po_delivery_status',
            'grpo_no',
            'grpo_date',
            'vendor_code',
            'item_code',
            'description',
            'uom',
            'qty',
            'unit_no',
            'project_code',
            'dept_code',
            'grpo_currency',
            'unit_price',
            'item_amount',
            'remarks',
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

        return $this->grpo_amount($date, $projects)->get();
        // return Grpo::whereMonth('grpo_date', Carbon::now())->get();
    }

    public function grpo_amount($date, $projects)
    {
        $incl_deptcode = ['40', '50', '60', '140', '200'];
        $excl_itemcode = ['EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%']; // , 
        foreach ($excl_itemcode as $e) {
            $excl_itemcode_arr[] = ['item_code', 'not like', $e];
        };

        $list = Grpo::whereMonth('po_delivery_date', $date)
            ->whereMonth('grpo_date', $date)
            ->whereIn('project_code', $projects)
            ->where('po_delivery_status', 'Delivered')
            ->whereIn('dept_code', $incl_deptcode)
            ->where($excl_itemcode_arr);

        return $list;
    }
}
