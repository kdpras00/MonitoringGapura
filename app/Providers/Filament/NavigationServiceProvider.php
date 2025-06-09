<?php

namespace App\Providers\Filament;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\EquipmentResource;
use App\Filament\Resources\MaintenanceResource;
use App\Filament\Resources\ApprovalResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\RoleResource;
use Illuminate\Support\Facades\Auth;

class NavigationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::serving(function () {
            // Get current user
            $user = Auth::user();

            if (!$user) {
                return;
            }

            // Get the panel
            $panel = Filament::getPanel('admin');

            // Register navigation items based on user role
            $userRole = $user->role ?? '';

            if ($userRole === 'admin') {
                // Admin can see all menus
                $this->registerAdminNavigation($panel);
            } elseif ($userRole === 'technician') {
                // Technician can only see maintenance
                $this->registerTechnicianNavigation($panel);
            } elseif ($userRole === 'supervisor') {
                // Supervisor melihat approval maintenance
                $this->registerSupervisorNavigation($panel);
            } elseif ($userRole === 'viewer') {
                // Viewer can only see reports
                $this->registerViewerNavigation($panel);
            } else {
                // Fallback for users without specific role
                $this->registerDefaultNavigation($panel);
            }
        });
    }

    private function registerAdminNavigation($panel): void
    {
        $panel->navigationItems([
            NavigationItem::make('Dashboard')
                ->icon('heroicon-o-home')
                ->activeIcon('heroicon-s-home')
                ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.pages.dashboard'))
                ->url(route('filament.admin.pages.dashboard')),

            // Equipment Management
            NavigationItem::make('Manage Equipment')
                ->icon('heroicon-o-cube')
                ->activeIcon('heroicon-s-cube')
                ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.resources.equipments.*'))
                ->url(route('filament.admin.resources.equipments.index')),

            // Maintenance Management
            NavigationItem::make('Manage Maintenance')
                ->icon('heroicon-o-wrench')
                ->activeIcon('heroicon-s-wrench')
                ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.resources.maintenances.*'))
                ->url(route('filament.admin.resources.maintenances.index')),
                
            // Approval Maintenance
            NavigationItem::make('Approval Maintenance')
                ->icon('heroicon-o-check-badge')
                ->activeIcon('heroicon-s-check-badge')
                ->badge(fn() => \App\Models\Maintenance::where('approval_status', 'pending')->count() ?: null)
                ->badgeColor('warning')
                ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.resources.approvals.*'))
                ->url(route('filament.admin.resources.approvals.index')),

            // Reports
            NavigationItem::make('View Reports')
                ->icon('heroicon-o-document-chart-bar')
                ->activeIcon('heroicon-s-document-chart-bar')
                ->isActiveWhen(fn(): bool => request()->routeIs('reports.*'))
                ->url(route('reports.maintenance')),

            // User Management
            NavigationItem::make('User Management')
                ->icon('heroicon-o-users')
                ->activeIcon('heroicon-s-users')
                ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.resources.users.*'))
                ->url(route('filament.admin.resources.users.index')),
        ]);
    }

    private function registerTechnicianNavigation($panel): void
    {
        $panel->navigationItems([
            NavigationItem::make('Dashboard')
                ->icon('heroicon-o-home')
                ->activeIcon('heroicon-s-home')
                ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.pages.dashboard'))
                ->url(route('filament.admin.pages.dashboard')),

            // Maintenance Management only
            NavigationItem::make('Manage Maintenance')
                ->icon('heroicon-o-wrench')
                ->activeIcon('heroicon-s-wrench')
                ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.resources.maintenances.*'))
                ->url(route('filament.admin.resources.maintenances.index')),
        ]);
    }

    private function registerViewerNavigation($panel): void
    {
        $panel->navigationItems([
            NavigationItem::make('Dashboard')
                ->icon('heroicon-o-home')
                ->activeIcon('heroicon-s-home')
                ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.pages.dashboard'))
                ->url(route('filament.admin.pages.dashboard')),

            // Reports only
            NavigationItem::make('View Reports')
                ->icon('heroicon-o-document-chart-bar')
                ->activeIcon('heroicon-s-document-chart-bar')
                ->isActiveWhen(fn(): bool => request()->routeIs('reports.*'))
                ->url(route('reports.maintenance')),
        ]);
    }

    private function registerDefaultNavigation($panel): void
    {
        $panel->navigationItems([
            NavigationItem::make('Dashboard')
                ->icon('heroicon-o-home')
                ->activeIcon('heroicon-s-home')
                ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.pages.dashboard'))
                ->url(route('filament.admin.pages.dashboard')),
        ]);
    }

    private function registerSupervisorNavigation($panel): void
    {
        $panel->navigationItems([
            NavigationItem::make('Dashboard')
                ->icon('heroicon-o-home')
                ->activeIcon('heroicon-s-home')
                ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.pages.dashboard'))
                ->url(route('filament.admin.pages.dashboard')),

            // Approval Maintenance
            NavigationItem::make('Approval Maintenance')
                ->icon('heroicon-o-check-badge')
                ->activeIcon('heroicon-s-check-badge')
                ->badge(fn() => \App\Models\Maintenance::where('approval_status', 'pending')->count() ?: null)
                ->badgeColor('warning')
                ->isActiveWhen(fn(): bool => request()->routeIs('filament.admin.resources.approvals.*'))
                ->url(route('filament.admin.resources.approvals.index')),
        ]);
    }
}
