<?php

namespace App\Filament\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleBasedNavigationPlugin implements Plugin
{
    public function getId(): string
    {
        return 'role-based-navigation';
    }

    public function register(Panel $panel): void
    {
        // Plugin registration logic
    }

    public function boot(Panel $panel): void
    {
        $panel->navigationItems([]);

        $panel->authGuard('web');

        $panel->navigationBuilder(function (NavigationBuilder $builder): NavigationBuilder {
            // Get current user and role
            $user = Auth::user();

            if (!$user) {
                return $builder;
            }

            $userRole = $user->role ?? '';

            // Log untuk debugging
            Log::info('User role detected: ' . $userRole);

            // Home untuk semua pengguna
            $builder->item(
                NavigationItem::make('Home')
                    ->icon('heroicon-o-home')
                    ->activeIcon('heroicon-s-home')
                    ->sort(1)
                    ->url('/admin/home')
            );

            // Menu untuk Admin
            if ($userRole === 'admin') {
                $builder->item(
                    NavigationItem::make('Manage Equipment')
                        ->icon('heroicon-o-cube')
                        ->activeIcon('heroicon-s-cube')
                        ->sort(2)
                        ->url('/admin/equipments')
                );

                $builder->item(
                    NavigationItem::make('Manage Maintenance')
                        ->icon('heroicon-o-wrench')
                        ->activeIcon('heroicon-s-wrench')
                        ->sort(3)
                        ->url('/admin/maintenances')
                );

                $builder->item(
                    NavigationItem::make('Spare Parts')
                        ->icon('heroicon-o-cog')
                        ->activeIcon('heroicon-s-cog')
                        ->sort(4)
                        ->url('/admin/spare-parts')
                );

                $builder->item(
                    NavigationItem::make('Technicians')
                        ->icon('heroicon-o-user-group')
                        ->activeIcon('heroicon-s-user-group')
                        ->sort(5)
                        ->url('/admin/technicians')
                );

                $builder->item(
                    NavigationItem::make('View Reports')
                        ->icon('heroicon-o-document-chart-bar')
                        ->activeIcon('heroicon-s-document-chart-bar')
                        ->sort(6)
                        ->url('/admin/reports/maintenance')
                );

                $builder->item(
                    NavigationItem::make('User Management')
                        ->icon('heroicon-o-users')
                        ->activeIcon('heroicon-s-users')
                        ->sort(7)
                        ->url('/admin/users')
                );
            }

            // Menu untuk Technician
            elseif ($userRole === 'technician') {
                $builder->item(
                    NavigationItem::make('Manage Equipment')
                        ->icon('heroicon-o-cube')
                        ->activeIcon('heroicon-s-cube')
                        ->sort(2)
                        ->url('/admin/equipments')
                );

                $builder->item(
                    NavigationItem::make('Spare Parts')
                        ->icon('heroicon-o-cog')
                        ->activeIcon('heroicon-s-cog')
                        ->sort(3)
                        ->url('/admin/spare-parts')
                );

                $builder->item(
                    NavigationItem::make('Technicians')
                        ->icon('heroicon-o-user-group')
                        ->activeIcon('heroicon-s-user-group')
                        ->sort(4)
                        ->url('/admin/technicians')
                );
            }

            // Viewer hanya melihat home yang sudah ditambahkan

            return $builder;
        });
    }
}
