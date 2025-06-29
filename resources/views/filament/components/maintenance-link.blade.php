@php
    $record = $getRecord();
    $maintenance = $record->getLatestMaintenanceAttribute();
@endphp

@if($maintenance)
    <div class="space-y-2">
        <a href="{{ auth()->user()->role === 'admin' ? route('filament.admin.resources.maintenances.view', ['record' => $maintenance->id]) : route('filament.technician.resources.maintenances.view', ['record' => $maintenance->id]) }}" 
           target="_blank" 
           class="filament-link inline-flex items-center justify-center gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset px-4 py-2 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">
            <span>Lihat Detail Maintenance</span>
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
        </a>
    </div>
@else
    <div class="text-gray-500 italic">
        Tidak ada maintenance terkait
    </div>
@endif 