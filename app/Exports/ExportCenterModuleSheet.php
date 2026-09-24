<?php

namespace App\Exports;

use App\Support\ExportCenterModules;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExportCenterModuleSheet implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithEvents, WithTitle, WithStrictNullComparison
{
    private array $module;

    public function __construct(
        private string $moduleCode,
        private Carbon $startDate,
        private Carbon $endDate
    ) {
        $this->module = ExportCenterModules::get($moduleCode);
    }

    public function title(): string
    {
        return $this->module['sheet_name'];
    }

    public function query()
    {
        $model = $this->module['model'];
        $dateColumn = $this->module['date_column'];

        return $model::query()
            ->select($this->module['columns'])
            ->whereBetween($dateColumn, [$this->startDate, $this->endDate])
            ->orderBy($dateColumn)
            ->orderBy('id');
    }

    public function headings(): array
    {
        return array_merge(['Periode'], $this->module['titles']);
    }

    public function map($row): array
    {
        $dateColumn = $this->module['date_column'];
        $periode = Carbon::parse($row->{$dateColumn})->format('Y-m');

        $values = [$periode];
        foreach ($this->module['columns'] as $column) {
            $values[] = $row->{$column};
        }

        return $values;
    }

    public function columnFormats(): array
    {
        // Columns holding amount/qty style numeric values across the four modules.
        $numericColumns = ['qty', 'unit_price', 'item_amount', 'total_po_price', 'po_with_vat'];
        $formats = [];

        foreach ($this->module['columns'] as $index => $column) {
            if (in_array($column, $numericColumns, true)) {
                // +1 for the Periode column that precedes $this->module['columns'],
                // +1 again to turn the 0-based $index into a 1-based column number.
                $columnLetter = Coordinate::stringFromColumnIndex($index + 2);
                $formats[$columnLetter] = '#,##0.00';
            }
        }

        return $formats;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestColumn = $sheet->getHighestColumn();

                $sheet->freezePane('A2');
                $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
                $sheet->setAutoFilter('A1:' . $highestColumn . '1');
            },
        ];
    }
}
