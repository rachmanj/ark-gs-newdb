<?php

namespace App\Exports;

use App\Models\Migi;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class MigiExport implements FromQuery, WithHeadings, WithChunkReading
{
    use Exportable;

    public function headings(): array
    {
        return [
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
            'Updated At'
        ];
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        // Filter for current month only
        return Migi::query()->whereMonth('posting_date', now()->month)
            ->whereYear('posting_date', now()->year);
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
