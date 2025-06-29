<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\LatestMaintenancesWidget;
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

            // Hapus semua widget yang terdaftar
            Filament::getPanel('admin')->widgets([]);
            
            // Hanya daftarkan widget yang diinginkan
            Filament::registerWidgets([
                DashboardStatsWidget::class,
                LatestMaintenancesWidget::class,
            ]);
        });
    }
} 