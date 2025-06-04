<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use App\Exports\MaintenanceReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export Data')
                ->icon('heroicon-o-archive-box')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $date = now()->format('Y-m');
                    $month = Carbon::parse($date)->month;

                    return Excel::download(new MaintenanceReportExport(now()->month, now()->year), 'maintenance-report.xlsx');
                }),
            Actions\Action::make('export-pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-archive-box')
                ->url(fn() => route('report.maintenance'))
                ->openUrlInNewTab(),
        ];
    }
}
