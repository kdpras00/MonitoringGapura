<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit Pengguna'),
            Actions\Action::make('toggleActive')
                ->label(fn () => $this->record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn () => $this->record->is_active ? 'danger' : 'success')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update([
                        'is_active' => !$this->record->is_active,
                    ]);
                    $this->refreshFormData([
                        'is_active',
                    ]);
                }),
        ];
    }
} 