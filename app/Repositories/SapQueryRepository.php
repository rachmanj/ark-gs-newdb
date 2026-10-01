<?php

namespace App\Repositories;

use App\Support\SapReportModules;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SapQueryRepository
{
    public function definition(int $ouqrKey): array
    {
        $row = DB::connection('sap_sql')->selectOne(
            'SELECT QName, QString FROM OUQR WHERE IntrnalKey = ?',
            [$ouqrKey]
        );

        if ($row === null) {
            throw new RuntimeException("SAP query definition not found for IntrnalKey {$ouqrKey}.");
        }

        return [
            'QName' => $row->QName,
            'QString' => $row->QString,
        ];
    }

    public function run(int $ouqrKey, ?string $fromDate, ?string $toDate): array
    {
        $definition = $this->definition($ouqrKey);
        $sql = $definition['QString'];

        $this->assertValidDate($fromDate);
        $this->assertValidDate($toDate);

        $module = $this->moduleForOuqrKey($ouqrKey);
        if ($module !== null && $module['uses_date_range'] && strpos($sql, '[%0]') === false) {
            throw new RuntimeException(
                "SAP query for IntrnalKey {$ouqrKey} requires date token [%0] but it is missing from QString."
            );
        }

        $sql = str_replace(
            ['[%0]', '[%1]'],
            [$fromDate ?? '', $toDate ?? ''],
            $sql
        );

        $sql = preg_replace('/\s+for\s+browse\s*$/i', '', rtrim($sql));

        $rows = DB::connection('sap_sql')->select($sql);

        return array_map(static function ($row) {
            return (array) $row;
        }, $rows);
    }

    private function assertValidDate(?string $date): void
    {
        if ($date === null) {
            return;
        }

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new RuntimeException("Invalid SAP report date format: {$date}");
        }
    }

    private function moduleForOuqrKey(int $ouqrKey): ?array
    {
        foreach (SapReportModules::all() as $module) {
            if ($module['ouqr_key'] === $ouqrKey) {
                return $module;
            }
        }

        return null;
    }
}
