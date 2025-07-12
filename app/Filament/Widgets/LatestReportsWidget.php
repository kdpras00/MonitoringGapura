<?php

namespace App\Filament\Widgets;

use App\Models\EquipmentReport;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestReportsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Laporan Kerusakan Terbaru';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                EquipmentReport::query()
                    ->where('status', '!=', 'resolved')
                    ->latest('reported_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Pelapor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('urgency_level')
                    ->label('Urgensi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('reported_at')
                    ->label('Dilaporkan')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in-review' => 'warning',
                        'confirmed' => 'success',
                        'rejected' => 'danger',
                        'resolved' => 'primary',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (EquipmentReport $record): string => route('filament.admin.resources.equipment-reports.view', $record))
                    ->icon('heroicon-o-eye'),
            ])
            ->paginated(false);
    }
} 