<?php

namespace App\Support;

use App\Models\Grpo;
use App\Models\Incoming;
use App\Models\Migi;
use App\Models\Powitheta;

/**
 * Single source of truth for the modules selectable in the Export Center.
 *
 * Column titles are copied verbatim from the existing per-module export
 * files (PowithetaExport, GrpoExport, MigiExport, IncomingExport) so the
 * Export Center sheets read the same way the existing exports do. The
 * `columns` list maps each title to the underlying database column.
 */
class ExportCenterModules
{
    public static function all(): array
    {
        return [
            'po' => [
                'code' => 'po',
                'label' => 'PO With ETA',
                'sheet_name' => 'PO With ETA',
                'model' => Powitheta::class,
                'date_column' => 'posting_date',
                'value_column' => 'item_amount',
                'titles' => [
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
                ],
                'columns' => [
                    'id',
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
                    'updated_at',
                ],
            ],
            'grpo' => [
                'code' => 'grpo',
                'label' => 'GRPO',
                'sheet_name' => 'GRPO',
                'model' => Grpo::class,
                'date_column' => 'grpo_date',
                'value_column' => 'item_amount',
                'titles' => [
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
                ],
                'columns' => [
                    'id',
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
                ],
            ],
            'migi' => [
                'code' => 'migi',
                'label' => 'MIGI',
                'sheet_name' => 'MIGI',
                'model' => Migi::class,
                'date_column' => 'posting_date',
                'value_column' => 'qty',
                'titles' => [
                    'ID',
                    'Posting Date',
                    'Doc Type',
                    'Doc No',
                    'Project Code',
                    'Dept Code',
                    'Item Code',
                    'Qty',
                    'UOM',
                    'Batch',
                    'Created At',
                    'Updated At',
                ],
                'columns' => [
                    'id',
                    'posting_date',
                    'doc_type',
                    'doc_no',
                    'project_code',
                    'dept_code',
                    'item_code',
                    'qty',
                    'uom',
                    'batch',
                    'created_at',
                    'updated_at',
                ],
            ],
            'incoming' => [
                'code' => 'incoming',
                'label' => 'Incoming Inventory',
                'sheet_name' => 'Incoming',
                'model' => Incoming::class,
                'date_column' => 'posting_date',
                'value_column' => 'qty',
                'titles' => [
                    '#',
                    'posting_date',
                    'Document Type',
                    'Document No',
                    'Project',
                    'dept_code',
                    'item_code',
                    'qty',
                    'uom',
                    'created_at',
                    'updated_at',
                ],
                'columns' => [
                    'id',
                    'posting_date',
                    'doc_type',
                    'doc_no',
                    'project_code',
                    'dept_code',
                    'item_code',
                    'qty',
                    'uom',
                    'created_at',
                    'updated_at',
                ],
            ],
        ];
    }

    public static function codes(): array
    {
        return array_keys(self::all());
    }

    public static function get(string $code): ?array
    {
        return self::all()[$code] ?? null;
    }
}
