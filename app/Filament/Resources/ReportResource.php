<?php

namespace App\Filament\Resources;

use App\Models\Maintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationLabel = 'Cetak Laporan';
    protected static ?string $navigationGroup = 'Administrator';
    protected static ?string $slug = 'reports';

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('schedule_date')
                    ->label('Jadwal')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('actual_date')
                    ->label('Tanggal Aktual')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Teknisi')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('maintenance_type')
                    ->label('Jenis')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'planned',
                        'primary' => 'in-progress',
                        'success' => 'completed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planned' => 'Planned',
                        'in-progress' => 'In Progress',
                        'completed' => 'Completed',
                    ]),

                Tables\Filters\SelectFilter::make('maintenance_type')
                    ->options([
                        'preventive' => 'Preventive',
                        'corrective' => 'Corrective',
                    ]),

                Tables\Filters\Filter::make('schedule_date')
                    ->form([
                        Forms\Components\DatePicker::make('from_date'),
                        Forms\Components\DatePicker::make('until_date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('schedule_date', '>=', $date),
                            )
                            ->when(
                                $data['until_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('schedule_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ReportResource\Pages\ListReports::route('/'),
            'view' => \App\Filament\Resources\ReportResource\Pages\ViewReport::route('/{record}'),
        ];
    }
}
