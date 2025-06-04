<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Models\Maintenance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaintenanceReportExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        return Maintenance::whereYear('schedule_date', $this->year)
            ->whereMonth('schedule_date', $this->month)
            ->get()
            ->map(function ($maintenance) {
                return [
                    'Equipment'      => $maintenance->equipment->name ?? 'N/A',
                    'Schedule Date'  => $maintenance->schedule_date->format('d M Y H:i') ?? 'N/A',
                    'Technician'     => $maintenance->technician->name ?? 'N/A',
                    'Status'         => ucfirst($maintenance->status ?? 'N/A'),
                    'Cost'           => $maintenance->cost ? number_format($maintenance->cost, 2, ',', '.') : '0,00',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Equipment',
            'Schedule Date',
            'Technician',
            'Status',
            'Cost (Rp)',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header Style
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => 'center'],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F2F2F2']],
            ],
            // Set Border untuk semua sel
            'A1:E1000' => [
                'borders' => [
                    'allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '000000']],
                ],
            ],
        ];
    }
}
