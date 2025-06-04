<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Forms\Components\CheckboxList;


class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
}
