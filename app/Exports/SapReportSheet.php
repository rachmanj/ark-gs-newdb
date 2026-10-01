<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class SapReportSheet implements FromArray, WithHeadings, WithStrictNullComparison, WithTitle
{
    public function __construct(
        private string $sheetName,
        private array $rows
    ) {
    }

    public function title(): string
    {
        return $this->sheetName;
    }

    public function headings(): array
    {
        if ($this->rows === []) {
            return [];
        }

        return array_keys($this->rows[0]);
    }

    public function array(): array
    {
        if ($this->rows === []) {
            return [];
        }

        $keys = array_keys($this->rows[0]);
        $data = [];

        foreach ($this->rows as $row) {
            $line = [];
            foreach ($keys as $key) {
                $line[] = array_key_exists($key, $row) ? $row[$key] : null;
            }
            $data[] = $line;
        }

        return $data;
    }
}
