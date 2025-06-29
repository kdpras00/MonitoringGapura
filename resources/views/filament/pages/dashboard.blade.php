<x-filament::page>
    <div class="flex flex-col gap-y-8">
        {{-- Header Widgets (DashboardStatsWidget) --}}
        <div>
            @livewire(\App\Filament\Widgets\DashboardStatsWidget::class)
        </div>
        
        {{-- Footer Widgets (LatestMaintenancesWidget) --}}
        <div>
            @livewire(\App\Filament\Widgets\LatestMaintenancesWidget::class)
        </div>
    </div>
</x-filament::page> 