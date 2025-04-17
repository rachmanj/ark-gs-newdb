<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyProductionTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Sample data with formatted dates
        return [
            [
                'date' => date('Y-m-d', strtotime('2023-01-01')),
                'project' => 'Project A',
                'day_shift' => 100,
                'night_shift' => 80,
                'mtd_ff_actual' => 95,
                'mtd_ff_plan' => 100,
                'mtd_rain_actual' => 20,
                'mtd_rain_plan' => 15,
                'mtd_haul_dist_actual' => 300,
                'mtd_haul_dist_plan' => 280,
                'limestone_day_shift' => 50,
                'limestone_swing_shift' => 40,
                'limestone_night_shift' => 30,
                'shalestone_day_shift' => 25,
                'shalestone_swing_shift' => 20,
                'shalestone_night_shift' => 15,
            ],
            [
                'date' => date('Y-m-d', strtotime('2023-01-02')),
                'project' => 'Project B',
                'day_shift' => 110,
                'night_shift' => 90,
                'mtd_ff_actual' => 92,
                'mtd_ff_plan' => 95,
                'mtd_rain_actual' => 18,
                'mtd_rain_plan' => 15,
                'mtd_haul_dist_actual' => 310,
                'mtd_haul_dist_plan' => 300,
                'limestone_day_shift' => 55,
                'limestone_swing_shift' => 45,
                'limestone_night_shift' => 35,
                'shalestone_day_shift' => 30,
                'shalestone_swing_shift' => 25,
                'shalestone_night_shift' => 20,
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'date',
            'project',
            'day_shift',
            'night_shift',
            'mtd_ff_actual',
            'mtd_ff_plan',
            'mtd_rain_actual',
            'mtd_rain_plan',
            'mtd_haul_dist_actual',
            'mtd_haul_dist_plan',
            'limestone_day_shift',
            'limestone_swing_shift',
            'limestone_night_shift',
            'shalestone_day_shift',
            'shalestone_swing_shift',
            'shalestone_night_shift',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
} 