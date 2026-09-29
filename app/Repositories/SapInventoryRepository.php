<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class SapInventoryRepository
{
    private const SQL_PATH = 'docs/sap-queries/inventory-all-warehouse.sql';

    /**
     * Runs the DDS inventory-all-warehouse query as-is and normalizes each
     * row into the associative shape used by the inventory snapshot layer.
     */
    public function fetchAll(): array
    {
        $rows = $this->fetchRawRows();

        return array_map(fn ($row) => $this->normalizeRow((array) $row), $rows);
    }

    /**
     * Isolated so a test double can override just the raw row source while
     * reusing normalizeRow() below for identical field-mapping behavior.
     */
    protected function fetchRawRows(): array
    {
        $sql = $this->readSql();

        return DB::connection('sap_sql')->select($sql, [0]);
    }

    private function readSql(): string
    {
        $path = base_path(self::SQL_PATH);

        if (! file_exists($path)) {
            throw new RuntimeException("SAP inventory SQL file not found: {$path}");
        }

        return file_get_contents($path);
    }

    protected function normalizeRow(array $row): array
    {
        return [
            'model_no' => $row['Model no'] ?? null,
            'unit_no' => $row['Unit No'] ?? null,
            'item_code' => $row['ItemCode'] ?? null,
            'item_name' => $row['ItemName'] ?? null,
            'uom' => $row['InvntryUom'] ?? null,
            'instock' => $row['Instock'] ?? 0,
            'committed' => $row['Committed'] ?? 0,
            'ordered' => $row['Ordered'] ?? 0,
            'currency' => $row['Currency'] ?? null,
            'last_price' => $row['Last Purchase Price'] ?? null,
            'total_value' => $row['Total'] ?? 0,
            'whs_code' => $row['WhsCode'] ?? null,
            'whs_name' => $row['WhsName'] ?? null,
            'project' => $row['U_MIS_Project'] ?? null,
            'status' => $row['Status'] ?? null,
        ];
    }
}
