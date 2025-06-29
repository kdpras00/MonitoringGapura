<?php

namespace App\Filament\Resources\VerificationResource\Pages;

use App\Filament\Resources\VerificationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListVerifications extends ListRecords
{
    protected static string $resource = VerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
} 