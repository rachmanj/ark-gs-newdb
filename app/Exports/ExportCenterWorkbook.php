<?php

namespace App\Exports;

use App\Support\ExportCenterModules;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExportCenterWorkbook implements WithMultipleSheets
{
    public function __construct(
        private array $moduleCodes,
        private Carbon $startDate,
        private Carbon $endDate
    ) {
    }

    public function sheets(): array
    {
        // Modules always appear in this fixed order, regardless of the order
        // they were submitted in, and only when they were actually selected.
        $orderedCodes = array_values(array_filter(
            ExportCenterModules::codes(),
            fn (string $code) => in_array($code, $this->moduleCodes, true)
        ));

        $sheets = [
            new ExportCenterSummarySheet($orderedCodes, $this->startDate, $this->endDate),
        ];

        foreach ($orderedCodes as $code) {
            $sheets[] = new ExportCenterModuleSheet($code, $this->startDate, $this->endDate);
        }

        return $sheets;
    }
}
