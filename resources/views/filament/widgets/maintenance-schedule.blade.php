<x-filament-widgets::widget class="bg-white rounded-lg shadow p-4">
    <h3 class="text-lg font-semibold mb-4">Jadwal Maintenance</h3>

    @forelse($maintenances as $maintenance)
        <div class="p-2 border-b last:border-b-0">
            <p class="text-sm text-gray-900 font-semibold">{{ $maintenance->equipment->name }}</p>
            <p class="text-xs text-gray-500">Dijadwalkan pada:
                {{ \Carbon\Carbon::parse($maintenance->schedule_date)->format('d M Y, H:i') }}</p>
        </div>
    @empty
        <p class="text-sm text-gray-500">Tidak ada jadwal maintenance dalam waktu dekat.</p>
    @endforelse
</x-filament-widgets::widget>
