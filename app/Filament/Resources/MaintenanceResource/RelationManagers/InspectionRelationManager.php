<?php

namespace App\Filament\Resources\MaintenanceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Inspection;

class InspectionRelationManager extends RelationManager
{
    protected static string $relationship = 'inspections';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('notes')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('inspection_date')
                    ->label('Tanggal Inspeksi')
                    ->dateTime(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'primary' => 'verified',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Teknisi'),
                Tables\Columns\ImageColumn::make('before_image')
                    ->label('Foto Sebelum')
                    ->circular(),
                Tables\Columns\ImageColumn::make('after_image')
                    ->label('Foto Setelah')
                    ->circular()
                    ->visible(fn ($record) => $record && isset($record->status) ? $record->status !== 'pending' : false),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->url(function ($livewire) {
                        $maintenance = $livewire->getOwnerRecord();
                        // Buat inspection baru
                        $inspection = new Inspection();
                        $inspection->equipment_id = $maintenance->equipment_id;
                        $inspection->technician_id = $maintenance->technician_id;
                        $inspection->inspection_date = $maintenance->schedule_date;
                        $inspection->schedule_date = $maintenance->schedule_date;
                        $inspection->status = 'pending';
                        $inspection->notes = "Dibuat dari jadwal maintenance: " . $maintenance->schedule_date->format('d M Y H:i');
                        $inspection->save();
                        
                        // Redirect ke halaman edit inspection
                        return route('filament.admin.resources.inspections.edit', ['record' => $inspection->id]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.inspections.view', ['record' => $record->id])),
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.inspections.edit', ['record' => $record->id])),
            ])
            ->bulkActions([
                //
            ]);
    }
} 