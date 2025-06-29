<?php

namespace App\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Panel;
use Filament\PanelProvider;
use App\Filament\Resources\InspectionResource;
use App\Filament\Resources\MaintenanceResource;
use App\Http\Middleware\TechnicianMiddleware;

class TechnicianPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('technician')
            ->path('technician')
            ->login()
            ->colors([
                'primary' => [
                    50 => '240, 253, 244',
                    100 => '220, 252, 231',
                    200 => '187, 247, 208',
                    300 => '134, 239, 172',
                    400 => '74, 222, 128',
                    500 => '34, 197, 94',
                    600 => '22, 163, 74',
                    700 => '21, 128, 61',
                    800 => '22, 101, 52',
                    900 => '20, 83, 45',
                    950 => '5, 46, 22',
                ],
            ])
            ->brandName('Teknisi - Monitoring Gapura')
            ->authGuard('web')
            ->discoverResources(false, app_path('Filament/Resources'))
            // Resource yang ditampilkan di navigasi
            ->navigationItems([
                InspectionResource::class,
            ])
            // Resource yang didaftarkan (dapat diakses melalui link) tetapi tidak tampil di navigasi
            ->resources([
                InspectionResource::class,
                MaintenanceResource::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                TechnicianMiddleware::class,
            ]);
    }
} 