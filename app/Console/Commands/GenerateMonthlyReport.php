<?php

namespace App\Console\Commands;

use App\Models\Maintenance;
use App\Exports\MaintenanceReportExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class GenerateMonthlyReport extends Command
{
    protected $signature = 'report:generate-monthly';
    protected $description = 'Generate monthly maintenance report';

    public function handle()
    {
        // Ambil bulan dan tahun sebelumnya
        $month = now()->subMonth()->month; // Pastikan dalam format integer
        $year = now()->subMonth()->year;

        $filename = "maintenance-report-{$year}-{$month}.xlsx";

        // Panggil export dengan bulan & tahun
        Excel::store(new MaintenanceReportExport($month, $year), $filename, 'public');

        $this->info("Report generated: storage/app/public/{$filename}");
    }
}
