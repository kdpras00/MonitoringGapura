{{-- resources/views/filament/resources/equipment/view.blade.php --}}
<x-filament::page>
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h1 class="text-2xl font-bold mb-4">Detail Equipment</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="mb-2"><strong class="font-medium">Nama:</strong> {{ $record->name }}</p>
                <p class="mb-2"><strong class="font-medium">Serial Number:</strong> {{ $record->serial_number }}</p>
                <p class="mb-2"><strong class="font-medium">Lokasi:</strong> {{ $record->location }}</p>
                <p class="mb-2">
                    <strong class="font-medium">Status:</strong> 
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $record->status === 'active' ? 'bg-green-100 text-green-800' : 
                        ($record->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($record->status) }}
                    </span>
                </p>
                <p class="mb-2"><strong class="font-medium">Tanggal Instalasi:</strong> {{ $record->installation_date ? $record->installation_date->format('d M Y') : '-' }}</p>
            </div>
            
            <div>
                @if($record->qr_code)
                <div class="flex flex-col items-center mb-4">
                    <div class="bg-gray-100 p-4 rounded-lg">
                        {!! QrCode::size(150)->generate(url('/q/' . $record->qr_code)) !!}
                    </div>
                    <p class="mt-2 text-sm text-gray-600">{{ $record->qr_code }}</p>
                    <a href="{{ route('equipment.print-qr', $record->id) }}" class="mt-2 text-blue-600 hover:underline text-sm" target="_blank">
                        Print QR Code
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    @if($record->specifications)
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h2 class="text-xl font-bold mb-2">Spesifikasi</h2>
        <p class="text-gray-700">{{ $record->specifications }}</p>
    </div>
    @endif
    
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h2 class="text-xl font-bold mb-2">Maintenance Checklist</h2>
        <ul class="list-disc pl-5 space-y-1">
            @php
                $checklist = null;
                if (!empty($record->checklist)) {
                    if (is_string($record->checklist)) {
                        $checklist = json_decode($record->checklist, true);
                    } else {
                        $checklist = $record->checklist;
                    }
                }
            @endphp
            
            @if(is_array($checklist) && count($checklist) > 0)
                @foreach($checklist as $item)
                    <li>{{ is_array($item) && isset($item['step']) ? $item['step'] : $item }}</li>
                @endforeach
            @else
                <li>No checklist items available</li>
            @endif
        </ul>
    </div>
    
    <div class="flex space-x-4 mt-6">
        <a href="{{ route('equipment.print-qr', $record->id) }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
           target="_blank">
            Print QR Code
        </a>
        
        @if($record->manual_url)
        <a href="{{ asset('storage/' . $record->manual_url) }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition"
           target="_blank">
            View Manual
        </a>
        @endif
        
        @if($record->sop_url)
        <a href="{{ asset('storage/' . $record->sop_url) }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
           target="_blank">
            View SOP Document
        </a>
        @endif
    </div>
</x-filament::page>
