<x-filament-widgets::widget class="bg-black rounded-lg shadow-lg p-4">
    <x-filament::section class="space-y-4">
        @forelse($notifications as $maintenance)
            <div class="flex items-center justify-between p-2 border-b border-gray-200">
                <p class="text-sm text-gray-900">
                    🔔 Maintenance <strong>{{ $maintenance->equipment->name }}</strong> dijadwalkan pada
                    <strong>{{ \Carbon\Carbon::parse($maintenance->next_service_date)->format('d-m-Y H:i') }}</strong>
                </p>
                <a href="{{ route('filament.admin.resources.maintenances.view', $maintenance->id) }}"
                    class="text-sm text-blue-500 hover:underline">
                    Lihat Detail
                </a>
            </div>
        @empty
            <p class="text-sm text-gray-500">Tidak ada maintenance dalam waktu dekat.</p>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
