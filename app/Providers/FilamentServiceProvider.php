<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use App\Filament\Pages\AdminDashboard;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Filament::serving(function () {
            // Mendaftarkan rute admin dashboard
            if (Route::has('filament.admin.pages.dashboard')) {
                Route::get('admin', function () {
                    return redirect()->route('filament.admin.pages.dashboard');
                })
                    ->middleware(['web', 'auth'])
                    ->name('filament.admin.pages.admin.dashboard');
            }

            // Biarkan widget terdaftar secara otomatis
        });
    }
}
