<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SummaryExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    protected $summaryData;
    protected $unitNumbers;
    protected $yearlyTotals;

    public function __construct($months, $unitNumbers, $yearlyTotals)
    {
        $this->summaryData = $months;
        $this->unitNumbers = $unitNumbers;
        $this->yearlyTotals = $yearlyTotals;
    }

    public function array(): array
    {
        $rows = [];
        
        // For each unit
        foreach ($this->unitNumbers as $unitNo) {
            $row = ['Unit ' . $unitNo];
            
            // Add monthly data
            foreach ($this->summaryData as $monthData) {
                $unitData = collect($monthData['units'])->firstWhere('unit_no', $unitNo);
                $row[] = $unitData['total_amount'] ?? '0.00';
            }
            
            // Add yearly total
            $row[] = $this->yearlyTotals[$unitNo]['yearly_total'] ?? '0.00';
            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        $headers = ['Unit No.'];
        
        // Add month names
        foreach ($this->summaryData as $monthData) {
            $headers[] = $monthData['month'];
        }
        
        // Add yearly total header
        $headers[] = 'Yearly Total';
        
        return $headers;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = chr(65 + count($this->summaryData));
        
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2B5797'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Style all cells borders
            'A1:' . $lastColumn . (count($this->unitNumbers) + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
            // Right align all numeric columns (months)
            'B2:' . chr(64 + count($this->summaryData)) . (count($this->unitNumbers) + 1) => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            // Yearly total column styling (separate to ensure it takes precedence)
            $lastColumn . '1:' . $lastColumn . (count($this->unitNumbers) + 1) => [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        $lastColumn = chr(65 + count($this->summaryData)); // Calculate last column letter
        return [
            'B:' . $lastColumn => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $lastColumn = chr(65 + count($this->summaryData));
                
                // Freeze panes
                $sheet->freezePane('B2');
                
                // Auto-filter
                $sheet->setAutoFilter('A1:' . $lastColumn . count($this->unitNumbers));
                
                // Set column width for Unit No.
                $sheet->getColumnDimension('A')->setWidth(15);
                
                // Zebra striping for all rows including yearly total
                for ($row = 2; $row <= (count($this->unitNumbers) + 1); $row++) {
                    if ($row % 2 == 0) {
                        // Even rows - lighter background
                        $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('F8F9FA');
                    }
                    
                    // Yearly total column - slightly different shade for all rows
                    $sheet->getStyle($lastColumn . $row)
                        ->getFill()
                        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB($row % 2 == 0 ? 'E2E9F7' : 'E8F0FE');
                }
            },
        ];
    }
} 