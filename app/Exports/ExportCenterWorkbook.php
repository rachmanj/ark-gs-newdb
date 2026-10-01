<?php

namespace App\Exports;

use App\Support\ExportCenterModules;
use App\Support\SapReportModules;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExportCenterWorkbook implements WithMultipleSheets
{
    public function __construct(
        private array $moduleCodes,
        private Carbon $startDate,
        private Carbon $endDate,
        private array $sapRowData = []
    ) {
    }

    public function sheets(): array
    {
        $orderedArkCodes = array_values(array_filter(
            ExportCenterModules::codes(),
            fn (string $code) => in_array($code, $this->moduleCodes, true)
        ));

        $orderedSapCodes = array_values(array_filter(
            SapReportModules::codes(),
            fn (string $code) => in_array($code, $this->moduleCodes, true)
        ));

        $sheets = [
            new ExportCenterSummarySheet(
                $orderedArkCodes,
                $orderedSapCodes,
                $this->startDate,
                $this->endDate,
                $this->sapRowData
            ),
        ];

        foreach ($orderedArkCodes as $code) {
            $sheets[] = new ExportCenterModuleSheet($code, $this->startDate, $this->endDate);
        }

        foreach ($orderedSapCodes as $code) {
            $module = SapReportModules::get($code);
            $sheets[] = new SapReportSheet(
                $module['sheet_name'],
                $this->sapRowData[$code] ?? []
            );
        }

        return $sheets;
    }
}
