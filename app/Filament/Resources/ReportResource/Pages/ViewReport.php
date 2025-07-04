<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Unduh PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(function () {
                    // Generate PDF dari data maintenance
                    $maintenance = $this->record;
                    $pdf = Pdf::loadView('reports.maintenance-single', ['maintenance' => $maintenance]);
                    
                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        'maintenance-report-' . $maintenance->id . '.pdf',
                        [
                            'Content-Type' => 'application/pdf',
                        ]
                    );
                })
                ->color('success')
        ];
    }
}
