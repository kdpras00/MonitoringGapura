<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceNextService extends TableWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Maintenance::with(['equipment', 'technician']) // Eager loading
                    ->where('schedule_date', '>=', now())
                    ->where('status', '!=', 'completed')
                    ->orderBy('schedule_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Perangkat')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('schedule_date')
                    ->label('Jadwal')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->description(fn($record) => $record->schedule_date->diffForHumans()),

                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Teknisi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'planned' => 'Terjadwal',
                        'in-progress' => 'Dalam Proses',
                        'completed' => 'Selesai',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'planned',
                        'primary' => 'in-progress',
                        'success' => 'completed',
                    ])
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planned' => 'Terjadwal',
                        'in-progress' => 'Dalam Proses',
                    ])
                    ->default('planned'),

                Tables\Filters\SelectFilter::make('technician')
                    ->relationship('technician', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_completed')
                    ->button()
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'completed')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'completed',
                            'actual_date' => now()
                        ]);
                    }),
            ])
            ->emptyStateHeading('Tidak ada jadwal maintenance')
            ->emptyStateDescription('Semua maintenance telah selesai atau belum terjadwal');
    }
}
