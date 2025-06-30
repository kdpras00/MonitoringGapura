<?php

namespace App\Filament\Resources\VerificationResource\Pages;

use App\Filament\Resources\VerificationResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use App\Models\Inspection;
use App\Models\Maintenance;
use Filament\Notifications\Notification;

class ViewVerification extends ViewRecord
{
    protected static string $resource = VerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify')
                ->label('Verifikasi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Textarea::make('verification_notes')
                        ->label('Catatan Verifikasi'),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    // Gunakan konstanta status dari model
                    $record->fill([
                        'status' => \App\Models\Inspection::STATUS_VERIFIED,
                        'verification_notes' => $data['verification_notes'] ?? null,
                        'verification_date' => now(),
                        'verified_by' => auth()->id()
                    ]);
                    $record->save();

                    // Update status maintenance jika ada
                    $maintenance = \App\Models\Maintenance::where('equipment_id', $record->equipment_id)
                        ->where('technician_id', $record->technician_id)
                        ->whereIn('status', ['in-progress', 'planned'])
                        ->first();

                    if ($maintenance) {
                        $maintenance->status = 'completed';
                        $maintenance->actual_date = now();
                        $maintenance->save();
                    }

                    Notification::make()
                        ->title('Inspection berhasil diverifikasi')
                        ->success()
                        ->send();

                    $this->redirect(VerificationResource::getUrl());
                })
                ->visible(fn () => $this->getRecord()->status === 'completed'),

            Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\Textarea::make('verification_notes')
                        ->label('Alasan Penolakan')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $record = $this->getRecord();
                    // Gunakan konstanta status dari model
                    $record->fill([
                        'status' => \App\Models\Inspection::STATUS_REJECTED,
                        'verification_notes' => $data['verification_notes'],
                        'verification_date' => now(),
                        'verified_by' => auth()->id()
                    ]);
                    $record->save();

                    Notification::make()
                        ->title('Inspection ditolak')
                        ->danger()
                        ->send();

                    $this->redirect(VerificationResource::getUrl());
                })
                ->visible(fn () => $this->getRecord()->status === 'completed'),
        ];
    }
}
