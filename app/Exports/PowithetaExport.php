<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PowithetaExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    public function headings(): array
    {
        return [
            '#',
            'PO Number',
            'Create Date',
            'Posting Date',
            'Vendor Code',
            'Vendor Name',
            'PR Number',
            'Item Code',
            'Description',
            'UOM',
            'Qty',
            'Unit No',
            'Project Code',
            'Dept Code',
            'PO Currency',
            'Unit Price',
            'Item Amount',
            'Total PO Price',
            'PO with VAT',
            'PO Status',
            'PO Delivery Status',
            'PO Delivery Date',
            'PO ETA',
            'Remarks',
            'Budget Type',
            'Created At',
            'Updated At',
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
            '025C',
            '026C',
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
            ->select(
                DB::raw('ROW_NUMBER() OVER (ORDER BY po_delivery_date DESC) as row_num'),
                'po_no',
                'create_date',
                'posting_date',
                'vendor_code',
                'vendor_name',
                'pr_no',
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
                'updated_at'
            )
            ->whereIn('dept_code', $incl_deptcode)
            ->where($excl_itemcode_arr)
            ->whereYear('po_delivery_date', $date)
            ->whereMonth('po_delivery_date', $date)
            ->whereIn('project_code', $project)
            ->where('po_status', '!=', 'Cancelled')
            ->where('po_delivery_status', '=', 'Delivered')
            ->orderBy('po_delivery_date', 'desc');

        return $list->get();
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2B5797'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'K' => NumberFormat::FORMAT_NUMBER,                // Qty
            'P' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2, // Unit Price
            'Q' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Item Amount
            'R' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total PO Price
            'S' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // PO with VAT
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Freeze the top row
                $sheet->freezePane('A2');

                // Auto filter
                $sheet->setAutoFilter('A1:' . $highestColumn . '1');

                // Set borders for all cells
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Zebra striping for rows
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        // Even rows - lighter background
                        $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('F8F9FA');
                    }
                }

                // Right align numeric columns
                $numericColumns = ['K', 'P', 'Q', 'R', 'S']; // Qty, Unit Price, Item Amount, Total PO Price, PO with VAT
                foreach ($numericColumns as $column) {
                    $sheet->getStyle($column . '2:' . $column . $highestRow)
                        ->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                }
            },
        ];
    }
}
