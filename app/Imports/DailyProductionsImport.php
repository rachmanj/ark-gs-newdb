<?php

namespace App\Imports;

use App\Models\DailyProduction;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DailyProductionsImport implements ToCollection, WithHeadingRow, WithValidation
{
    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Convert date properly
                $date = $this->parseDate($row['date']);
                
                // Skip rows with invalid dates
                if (!$date) {
                    continue;
                }

                DailyProduction::create([
                    'date' => $date,
                    'project' => $row['project'],
                    'day_shift' => $row['day_shift'] ?? null,
                    'night_shift' => $row['night_shift'] ?? null,
                    'mtd_ff_actual' => $row['mtd_ff_actual'] ?? null,
                    'mtd_ff_plan' => $row['mtd_ff_plan'] ?? null,
                    'mtd_rain_actual' => $row['mtd_rain_actual'] ?? null,
                    'mtd_rain_plan' => $row['mtd_rain_plan'] ?? null,
                    'mtd_haul_dist_actual' => $row['mtd_haul_dist_actual'] ?? null,
                    'mtd_haul_dist_plan' => $row['mtd_haul_dist_plan'] ?? null,
                    'limestone_day_shift' => $row['limestone_day_shift'] ?? null,
                    'limestone_swing_shift' => $row['limestone_swing_shift'] ?? null,
                    'limestone_night_shift' => $row['limestone_night_shift'] ?? null,
                    'shalestone_day_shift' => $row['shalestone_day_shift'] ?? null,
                    'shalestone_swing_shift' => $row['shalestone_swing_shift'] ?? null,
                    'shalestone_night_shift' => $row['shalestone_night_shift'] ?? null,
                    'created_by' => Auth::id(),
                ]);
            } catch (\Exception $e) {
                // Log the error but continue processing other rows
                \Log::error('Error processing row in import: ' . $e->getMessage());
                \Log::error('Row data: ' . json_encode($row->toArray()));
            }
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'date' => 'required',
            'project' => 'required',
            'day_shift' => 'nullable|numeric',
            'night_shift' => 'nullable|numeric',
            'mtd_ff_actual' => 'nullable|numeric',
            'mtd_ff_plan' => 'nullable|numeric',
            'mtd_rain_actual' => 'nullable|numeric',
            'mtd_rain_plan' => 'nullable|numeric',
            'mtd_haul_dist_actual' => 'nullable|numeric',
            'mtd_haul_dist_plan' => 'nullable|numeric',
            'limestone_day_shift' => 'nullable|numeric',
            'limestone_swing_shift' => 'nullable|numeric',
            'limestone_night_shift' => 'nullable|numeric',
            'shalestone_day_shift' => 'nullable|numeric',
            'shalestone_swing_shift' => 'nullable|numeric',
            'shalestone_night_shift' => 'nullable|numeric',
        ];
    }

    /**
     * Parse date from multiple formats
     * 
     * @param mixed $value
     * @return Carbon|null
     */
    private function parseDate($value)
    {
        // Skip empty values
        if (empty($value)) {
            return null;
        }

        // Try parsing as Excel date (numeric format)
        if (is_numeric($value)) {
            try {
                // Ensuring it's treated as a float to avoid floor() errors
                return Carbon::instance(Date::excelToDateTimeObject((float)$value));
            } catch (\Exception $e) {
                // Failed to parse as Excel date
            }
        }

        // Try parsing as Y-m-d format
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value);
        }

        // Try parsing as d/m/Y format
        if (is_string($value) && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return Carbon::createFromFormat('d/m/Y', $value);
        }

        // Last resort, let Carbon try to figure it out
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
} 