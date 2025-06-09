<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PredictiveMaintenanceResource\Pages;
use App\Filament\Resources\PredictiveMaintenanceResource\RelationManagers;
use App\Models\PredictiveMaintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Support\Colors\Color;

class PredictiveMaintenanceResource extends Resource
{
    protected static ?string $model = PredictiveMaintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Predictive Maintenance';
    protected static ?string $navigationGroup = 'Maintenance';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('equipment_id')
                    ->relationship('equipment', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\DateTimePicker::make('last_maintenance_date')
                    ->label('Maintenance Terakhir'),
                Forms\Components\DateTimePicker::make('next_maintenance_date')
                    ->label('Prediksi Maintenance Berikutnya')
                    ->required(),
                Forms\Components\Slider::make('condition_score')
                    ->label('Skor Kondisi')
                    ->min(0)
                    ->max(100)
                    ->step(1)
                    ->required(),
                Forms\Components\Select::make('recommendation')
                    ->label('Rekomendasi')
                    ->options([
                        'Routine maintenance recommended' => 'Routine maintenance recommended',
                        'Inspection needed' => 'Inspection needed',
                        'Immediate maintenance required' => 'Immediate maintenance required',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_maintenance_date')
                    ->label('Maintenance Terakhir')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('next_maintenance_date')
                    ->label('Prediksi Berikutnya')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('condition_score')
                    ->label('Skor Kondisi')
                    ->sortable()
                    ->numeric(0)
                    ->suffix('/100')
                    ->color(function ($state) {
                        if ($state > 80) return 'success';
                        if ($state > 60) return 'warning';
                        return 'danger';
                    }),
                TextColumn::make('recommendation')
                    ->label('Rekomendasi')
                    ->wrap()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('condition')
                    ->options([
                        'good' => 'Kondisi Baik (>80)',
                        'warning' => 'Perlu Perhatian (61-80)',
                        'critical' => 'Kritis (<60)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        
                        return match ($data['value']) {
                            'good' => $query->where('condition_score', '>', 80),
                            'warning' => $query->whereBetween('condition_score', [61, 80]),
                            'critical' => $query->where('condition_score', '<=', 60),
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPredictiveMaintenances::route('/'),
            'create' => Pages\CreatePredictiveMaintenance::route('/create'),
            'edit' => Pages\EditPredictiveMaintenance::route('/{record}/edit'),
        ];
    }
}
