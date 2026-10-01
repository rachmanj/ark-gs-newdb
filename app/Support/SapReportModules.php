<?php

namespace App\Support;

class SapReportModules
{
    public static function all(): array
    {
        return [
            'sap01' => [
                'code' => 'sap01',
                'label' => 'Purchase Order Complete (01)',
                'sheet_name' => '01. PO Complete',
                'ouqr_key' => 836,
                'uses_date_range' => true,
                'group' => 'prc_logistik',
            ],
            'sap02' => [
                'code' => 'sap02',
                'label' => 'PR Completed SAP (02)',
                'sheet_name' => '02. PR Completed SAP',
                'ouqr_key' => 837,
                'uses_date_range' => true,
                'group' => 'prc_logistik',
            ],
            'sap03' => [
                'code' => 'sap03',
                'label' => 'Penerimaan Barang GRPO (03)',
                'sheet_name' => '03. Penerimaan Barang GRPO',
                'ouqr_key' => 838,
                'uses_date_range' => true,
                'group' => 'prc_logistik',
            ],
            'sap04' => [
                'code' => 'sap04',
                'label' => 'List ITO (04)',
                'sheet_name' => '04. List ITO',
                'ouqr_key' => 839,
                'uses_date_range' => true,
                'group' => 'prc_logistik',
            ],
            'sap05' => [
                'code' => 'sap05',
                'label' => 'Goods Receipt (05)',
                'sheet_name' => '05. Goods Receipt',
                'ouqr_key' => 840,
                'uses_date_range' => true,
                'group' => 'prc_logistik',
            ],
            'sap07' => [
                'code' => 'sap07',
                'label' => 'Material Return (07)',
                'sheet_name' => '07. Material Return',
                'ouqr_key' => 842,
                'uses_date_range' => true,
                'group' => 'prc_logistik',
            ],
            'sap08' => [
                'code' => 'sap08',
                'label' => 'List Material Requisition (08)',
                'sheet_name' => '08. List Material Requisition',
                'ouqr_key' => 844,
                'uses_date_range' => true,
                'group' => 'prc_logistik',
            ],
            'sap10' => [
                'code' => 'sap10',
                'label' => 'Inventory In All Warehouse (10)',
                'sheet_name' => '10. Inventory All Whs',
                'ouqr_key' => 845,
                'uses_date_range' => false,
                'group' => 'prc_logistik',
            ],
            'sap11' => [
                'code' => 'sap11',
                'label' => 'Backdate Report Complete (11)',
                'sheet_name' => '11. Backdate Report',
                'ouqr_key' => 846,
                'uses_date_range' => true,
                'group' => 'prc_logistik',
            ],
            'sap12' => [
                'code' => 'sap12',
                'label' => 'List DCR (12)',
                'sheet_name' => '12. List DCR',
                'ouqr_key' => 847,
                'uses_date_range' => true,
                'group' => 'prc_logistik',
            ],
            'sap13' => [
                'code' => 'sap13',
                'label' => 'Pemakaian Sparepart and Purchase Service (13)',
                'sheet_name' => '13. Pemakaian Sparepart',
                'ouqr_key' => 856,
                'uses_date_range' => true,
                'group' => 'prc_logistik',
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

    public static function groups(): array
    {
        $grouped = [];

        foreach (self::all() as $module) {
            $grouped[$module['group']][] = $module;
        }

        return $grouped;
    }
}
