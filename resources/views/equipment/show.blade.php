@extends('layouts.app')

@section('content')
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-lg">
        <h3 class="text-3xl font-bold mb-6 text-gray-800 text-center">QR Code Equipment</h3>

        <div class="grid gap-4 text-lg text-gray-700">
            <p><span class="font-semibold">Nama:</span> {{ $equipment->name }}</p>
            <p><span class="font-semibold">Serial Number:</span> {{ $equipment->serial_number }}</p>
            <p><span class="font-semibold">Lokasi:</span> {{ $equipment->location }}</p>
            <p>
                <span class="font-semibold">Status:</span>
                <span
                    class="px-3 py-1 rounded-lg text-white
                    {{ $equipment->status === 'active' ? 'bg-green-500' : ($equipment->status === 'maintenance' ? 'bg-yellow-500' : 'bg-red-500') }}">
                    {{ ucfirst($equipment->status) }}
                </span>
            </p>
            
            @if(count($equipment->checklist_array) > 0)
            <div class="mt-4">
                <p class="font-semibold mb-2">Maintenance Checklist:</p>
                <ul class="list-disc pl-5">
                    @foreach($equipment->checklist_array as $item)
                        <li>{{ is_array($item) && isset($item['step']) ? $item['step'] : $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <div class="mt-6 flex justify-center">
            {{-- Menampilkan QR Code --}}
            <div class="bg-gray-100 p-6 rounded-lg shadow-inner">
                {!! QrCode::size(200)->generate(url('/q/' . $equipment->qr_code)) !!}
                <p class="text-center mt-2 text-gray-600">{{ $equipment->qr_code }}</p>
            </div>
        </div>

        <div class="mt-8 text-center">
            {{-- <a href="{{ route('filament.resources.equipment.view', $equipment) }}">View Equipment</a> --}}
            <a href="{{ route('dashboard') }}"
                class="px-6 py-3 bg-blue-600 text-white text-lg font-semibold rounded-lg shadow-md hover:bg-blue-700 transition-all duration-300">
                Kembali
            </a>
        </div>
    </div>
@endsection
