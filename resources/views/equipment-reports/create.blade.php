@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">Laporkan Kerusakan Peralatan</h2>
        
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('equipment-reports.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label for="equipment_id" class="block text-gray-700 font-bold mb-2">Pilih Peralatan</label>
                <select id="equipment_id" name="equipment_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Peralatan --</option>
                    @foreach ($equipments as $equipment)
                        <option value="{{ $equipment->id }}">{{ $equipment->name }} ({{ $equipment->code }})</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-bold mb-2">Deskripsi Kerusakan</label>
                <textarea id="description" name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            </div>
            
            <div class="mb-4">
                <label for="urgency_level" class="block text-gray-700 font-bold mb-2">Tingkat Urgensi</label>
                <select id="urgency_level" name="urgency_level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="low">Rendah - Masih bisa beroperasi</option>
                    <option value="medium">Sedang - Beroperasi tapi terbatas</option>
                    <option value="high">Tinggi - Tidak bisa beroperasi</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label for="location" class="block text-gray-700 font-bold mb-2">Lokasi</label>
                <input type="text" id="location" name="location" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('location') }}">
            </div>
            
            <div class="mb-6">
                <label for="image" class="block text-gray-700 font-bold mb-2">Foto Kerusakan (opsional)</label>
                <input type="file" id="image" name="image" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Kirim Laporan
                </button>
                <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-800">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection 