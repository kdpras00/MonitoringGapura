<?php

namespace App\Filament\Resources\SupervisorRoleResource\Pages;

use App\Filament\Resources\SupervisorRoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupervisorRoles extends ListRecords
{
    protected static string $resource = SupervisorRoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
