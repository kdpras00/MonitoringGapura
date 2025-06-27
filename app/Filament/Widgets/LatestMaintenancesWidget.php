<?php

namespace App\Filament\Widgets;

use App\Models\Maintenance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestMaintenancesWidget extends BaseWidget
{
    protected static ?string $heading = 'Maintenance Terbaru';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 'full';
    
    protected function getTableQuery(): Builder
    {
        return Maintenance::query()->latest()->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('equipment.name')
                ->label('Equipment')
                ->searchable(),
            Tables\Columns\TextColumn::make('schedule_date')
                ->label('Jadwal')
                ->dateTime(),
            Tables\Columns\TextColumn::make('technician.name')
                ->label('Teknisi'),
            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'secondary' => 'planned',
                    'warning' => 'in-progress',
                    'success' => 'completed',
                ]),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime()
                ->sortable(),
        ];
    }
} 