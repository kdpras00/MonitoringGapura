<?php

namespace App\Filament\Resources\EquipmentResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TechnicianRelationManager extends RelationManager
{
    protected static string $relationship = 'technician';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Technician Name')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    protected function getTableQuery(): Builder
    {
        /** @var Model $ownerRecord */
        $ownerRecord = $this->getOwnerRecord();

        if (!$ownerRecord) {
            throw new \Exception('Owner record is null in TechnicianRelationManager');
        }

        return $this->getRelationship()->getQuery();
    }
}
