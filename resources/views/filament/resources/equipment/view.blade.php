{{-- resources/views/filament/resources/equipment/view.blade.php --}}
<x-filament::page>
    <h1 class="text-2xl font-bold">Detail Equipment</h1>
    <p><strong>Nama:</strong> {{ $record->name }}</p>
    <p><strong>Serial Number:</strong> {{ $record->serial_number }}</p>
    <p><strong>Lokasi:</strong> {{ $record->location }}</p>
    <p><strong>Status:</strong> {{ ucfirst($record->status) }}</p>
</x-filament::page>
