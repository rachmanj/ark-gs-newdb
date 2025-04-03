<?php

namespace App\Exports;

use App\Models\Powitheta;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PowithetaSummaryExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithColumnFormatting, WithEvents
{
    protected $summaryData;
    protected $projects;
    protected $totalsByProject;

    public function __construct()
    {
        $date = Carbon::now();
        $this->prepareSummaryData($date);
    }

    private function prepareSummaryData($date)
    {
        // Define the projects to include
        $this->projects = ['017C', '021C', '022C', '023C', '025C', 'APS'];
        
        // Define department codes to include
        $incl_deptcode = ['40', '50', '60', '140', '200'];
        
        // Define item codes to exclude
        $excl_itemcode = ['EX%', 'FU%', 'PB%', 'Pp%', 'SA%', 'SO%', 'SV%'];
        foreach ($excl_itemcode as $e) {
            $excl_itemcode_arr[] = ['item_code', 'not like', $e];
        }

        // Group data by month and project
        $this->summaryData = [];
        $this->totalsByProject = [];
        
        // Initialize total by project
        foreach ($this->projects as $project) {
            $this->totalsByProject[$project] = 0;
        }
        
        // Get data for the past 12 months
        for ($i = 0; $i < 12; $i++) {
            $monthDate = clone $date;
            $monthDate->subMonths($i);
            
            $monthName = $monthDate->format('M Y');
            $monthProjects = [];
            
            // Get data for each project in this month
            foreach ($this->projects as $project) {
                $monthlyAmount = Powitheta::whereYear('po_delivery_date', $monthDate->year)
                    ->whereMonth('po_delivery_date', $monthDate->month)
                    ->where('project_code', $project)
                    ->where('po_delivery_status', 'Delivered')
                    ->where('po_status', '!=', 'Cancelled')
                    ->whereIn('dept_code', $incl_deptcode)
                    ->where($excl_itemcode_arr)
                    ->sum('item_amount');
                
                $monthProjects[] = [
                    'project_code' => $project,
                    'total_amount' => $monthlyAmount
                ];
                
                // Add to project totals
                $this->totalsByProject[$project] += $monthlyAmount;
            }
            
            $this->summaryData[] = [
                'month' => $monthName,
                'projects' => $monthProjects
            ];
        }
        
        // Reverse to show oldest to newest
        $this->summaryData = array_reverse($this->summaryData);
    }

    public function array(): array
    {
        $rows = [];
        
        // For each project
        foreach ($this->projects as $project) {
            $row = [$project];
            
            // Add monthly data
            foreach ($this->summaryData as $monthData) {
                $projectData = collect($monthData['projects'])->firstWhere('project_code', $project);
                $row[] = $projectData['total_amount'] ?? 0;
            }
            
            // Add yearly total
            $row[] = $this->totalsByProject[$project] ?? 0;
            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        $headers = ['Project'];
        
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
        $lastColumn = chr(65 + count($this->summaryData) + 1); // +1 for Yearly Total column
        
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
            'A1:' . $lastColumn . (count($this->projects) + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ],
            // Right align all numeric columns (months)
            'B2:' . $lastColumn . (count($this->projects) + 1) => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
            // Yearly total column styling (separate to ensure it takes precedence)
            $lastColumn . '1:' . $lastColumn . (count($this->projects) + 1) => [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F0FE'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        $lastColumn = chr(65 + count($this->summaryData) + 1); // Calculate last column letter
        return [
            'B:' . $lastColumn => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $lastColumn = chr(65 + count($this->summaryData) + 1);
                
                // Freeze panes
                $sheet->freezePane('B2');
                
                // Auto-filter
                $sheet->setAutoFilter('A1:' . $lastColumn . count($this->projects));
                
                // Set column width for Project column
                $sheet->getColumnDimension('A')->setWidth(15);
                
                // Add a total row at the bottom
                $totalRow = count($this->projects) + 2;
                $sheet->setCellValue('A' . $totalRow, 'TOTAL');
                
                // Style the total row
                $sheet->getStyle('A' . $totalRow . ':' . $lastColumn . $totalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2'],
                    ],
                    'borders' => [
                        'outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM],
                    ]
                ]);
                
                // Add sum formulas for each month and yearly total
                for ($col = 66; $col <= 64 + count($this->summaryData) + 1; $col++) {
                    $colLetter = chr($col);
                    $sheet->setCellValue($colLetter . $totalRow, '=SUM(' . $colLetter . '2:' . $colLetter . ($totalRow - 1) . ')');
                }
                
                // Zebra striping for rows
                for ($row = 2; $row <= (count($this->projects) + 1); $row++) {
                    if ($row % 2 == 0) {
                        // Even rows - lighter background
                        $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('F8F9FA');
                    }
                }
            },
        ];
    }
} 