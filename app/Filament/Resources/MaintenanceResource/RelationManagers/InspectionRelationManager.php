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
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
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
                        
                        // Cek apakah sudah ada inspection dengan equipment_id dan technician_id yang sama
                        $existingInspection = Inspection::where('equipment_id', $maintenance->equipment_id)
                            ->where('technician_id', $maintenance->technician_id)
                            ->where('status', 'pending')
                            ->first();
                        
                        // Jika sudah ada, gunakan inspection yang sudah ada
                        if ($existingInspection) {
                            // Tambahkan notifikasi
                            \Filament\Notifications\Notification::make()
                                ->title('Inspection sudah ada')
                                ->body("Inspection untuk equipment dan teknisi ini sudah ada dengan status pending.")
                                ->warning()
                                ->send();
                                
                            return route('filament.admin.resources.inspections.view', ['record' => $existingInspection->id]);
                        }
                        
                        // Buat inspection baru jika belum ada
                        $inspection = new Inspection();
                        $inspection->equipment_id = $maintenance->equipment_id;
                        $inspection->technician_id = $maintenance->technician_id;
                        $inspection->maintenance_id = $maintenance->id; // Tambahkan maintenance_id
                        $inspection->inspection_date = $maintenance->schedule_date;
                        $inspection->schedule_date = $maintenance->schedule_date;
                        $inspection->status = 'pending';
                        $inspection->notes = "Dibuat dari jadwal maintenance: " . $maintenance->schedule_date->format('d M Y H:i');
                        $inspection->save();
                        
                        // Redirect ke halaman edit inspection
                        return route('filament.admin.resources.inspections.view', ['record' => $inspection->id]);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.inspections.view', ['record' => $record->id])),
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.inspections.view', ['record' => $record->id]))
                    ->visible(fn ($record) => !in_array($record->status, ['verified', 'rejected'])),
            ])
            ->bulkActions([
                //
            ]);
    }
} 