<?php

namespace App\Exports;

use App\Support\ExportCenterModules;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportCenterSummarySheet implements FromArray, WithHeadings, WithStyles, WithTitle
{
    /** @var int[] 1-based row numbers (relative to the data, header excluded) that hold a TOTAL line. */
    private array $totalRowIndexes = [];

    public function __construct(
        private array $moduleCodes,
        private Carbon $startDate,
        private Carbon $endDate
    ) {
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function headings(): array
    {
        return ['Modul', 'Periode', 'Jumlah Baris', 'Total Amount or Qty'];
    }

    public function array(): array
    {
        $rows = [];
        $rowNumber = 0;

        foreach ($this->moduleCodes as $moduleCode) {
            $module = ExportCenterModules::get($moduleCode);
            if (! $module) {
                continue;
            }

            $model = $module['model'];
            $dateColumn = $module['date_column'];
            $valueColumn = $module['value_column'];

            $moduleTotalRows = 0;
            $moduleTotalValue = 0;

            $period = $this->startDate->copy()->startOfMonth();
            while ($period->lte($this->endDate)) {
                $periodStart = $period->copy()->startOfMonth();
                $periodEnd = $period->copy()->endOfMonth();

                $rowCount = $model::query()
                    ->whereBetween($dateColumn, [$periodStart, $periodEnd])
                    ->count();

                $rowSum = $model::query()
                    ->whereBetween($dateColumn, [$periodStart, $periodEnd])
                    ->sum($valueColumn);

                $rows[] = [$module['label'], $period->format('Y-m'), $rowCount, $rowSum];
                $rowNumber++;

                $moduleTotalRows += $rowCount;
                $moduleTotalValue += $rowSum;

                $period->addMonth();
            }

            $rows[] = [$module['label'], 'TOTAL', $moduleTotalRows, $moduleTotalValue];
            $rowNumber++;
            $this->totalRowIndexes[] = $rowNumber;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach ($this->totalRowIndexes as $rowNumber) {
            $excelRow = $rowNumber + 1; // +1 because row 1 is the header
            $sheet->getStyle('A' . $excelRow . ':D' . $excelRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $excelRow . ':D' . $excelRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setRGB('F0F0F0');
        }

        return [];
    }
}
