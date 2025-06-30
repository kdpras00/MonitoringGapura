<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class AdminDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationLabel = 'Dashboard';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $title = 'Monitoring Maintenance Gapura Angkasa';

    // Explicitly set the correct route for admin panel with a unique name
    protected static ?string $slug = 'admin-home';

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Dashboard hanya untuk admin
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    // Kosongkan widget untuk mencegah duplikasi
    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
}
