<?php

namespace App\Filament\Resources\ApprovalResource\Pages;

use App\Filament\Resources\ApprovalResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions;

class ListApprovals extends ListRecords
{
    protected static string $resource = ApprovalResource::class;

    protected function getActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Approval Maintenance';
    }
} 