<?php

namespace App\Filament\Resources\SparePartResource\Pages;

use App\Filament\Resources\SparePartResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditSparePart extends EditRecord
{
    protected static string $resource = SparePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
