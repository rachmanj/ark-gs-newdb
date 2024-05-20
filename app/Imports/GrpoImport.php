<?php

namespace App\Imports;

use App\Models\Grpo;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GrpoImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Grpo([
            'po_no' => $row['po_no'],
            'po_date' => $this->convert_date($row['po_date']),
            'po_delivery_date' => $this->convert_date($row['po_delivery_date']),
            'grpo_date' => $this->convert_date($row['grpo_date']),
            'po_delivery_status' => $row['po_delivery_status'],
            'grpo_no' => $row['grpo_no'],
            'vendor_code' => $row['vendor_code'],
            'unit_no' => $row['unit_no'],
            'item_code' => $row['item_code'],
            'uom' => $row['uom'],
            'description' => $row['description'],
            'qty' => $row['qty'],
            'grpo_currency' => $row['grpo_currency'],
            'unit_price' => $row['unit_price'],
            'item_amount' => $row['item_amount'],
            'project_code' => $row['project_code'],
            'dept_code' => $row['dept_code'],
            'remarks' => $row['remarks'],
            'batch' => 1
        ]);
    }

    public function convert_date($date)
    {
        if ($date) {
            $year = substr($date, 6, 4);
            $month = substr($date, 3, 2);
            $day = substr($date, 0, 2);
            $new_date = $year . '-' . $month . '-' . $day;
            return $new_date;
        } else {
            return null;
        }
    }
}
