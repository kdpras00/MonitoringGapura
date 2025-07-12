@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Detail Laporan Kerusakan #{{ $equipmentReport->id }}</h2>
            <div>
                <a href="{{ route('equipment-reports.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    Kembali
                </a>
                @if ($equipmentReport->reporter_id === auth()->id() && $equipmentReport->status === 'pending')
                    <a href="{{ route('equipment-reports.edit', $equipmentReport) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded ml-2">
                        Edit
                    </a>
                @endif
            </div>
        </div>
        
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold mb-4 border-b pb-2">Informasi Laporan</h3>
                
                <div class="mb-4">
                    <p class="text-gray-600">Equipment:</p>
                    <p class="font-semibold">{{ $equipmentReport->equipment->name ?? 'N/A' }}</p>
                </div>
                
                <div class="mb-4">
                    <p class="text-gray-600">Dilaporkan oleh:</p>
                    <p class="font-semibold">{{ $equipmentReport->reporter->name ?? 'N/A' }}</p>
                </div>
                
                <div class="mb-4">
                    <p class="text-gray-600">Tanggal Laporan:</p>
                    <p class="font-semibold">{{ $equipmentReport->reported_at->format('d M Y H:i') }}</p>
                </div>
                
                <div class="mb-4">
                    <p class="text-gray-600">Lokasi:</p>
                    <p class="font-semibold">{{ $equipmentReport->location ?? 'Tidak disebutkan' }}</p>
                </div>
                
                <div class="mb-4">
                    <p class="text-gray-600">Tingkat Urgensi:</p>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold 
                        {{ $equipmentReport->urgency_level === 'high' ? 'bg-red-100 text-red-800' : 
                           ($equipmentReport->urgency_level === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                        {{ $equipmentReport->urgency_level === 'high' ? 'Tinggi' : 
                           ($equipmentReport->urgency_level === 'medium' ? 'Sedang' : 'Rendah') }}
                    </span>
                </div>
                
                <div class="mb-4">
                    <p class="text-gray-600">Status:</p>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold 
                        {{ $equipmentReport->status === 'pending' ? 'bg-gray-100 text-gray-800' : 
                           ($equipmentReport->status === 'in-review' ? 'bg-yellow-100 text-yellow-800' : 
                           ($equipmentReport->status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                           ($equipmentReport->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'))) }}">
                        {{ $equipmentReport->status === 'pending' ? 'Pending' : 
                           ($equipmentReport->status === 'in-review' ? 'Sedang Ditinjau' : 
                           ($equipmentReport->status === 'confirmed' ? 'Dikonfirmasi' : 
                           ($equipmentReport->status === 'rejected' ? 'Ditolak' : 'Selesai'))) }}
                    </span>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold mb-4 border-b pb-2">Deskripsi Kerusakan</h3>
                <div class="bg-gray-50 p-4 rounded-md mb-6">
                    <p>{{ $equipmentReport->description }}</p>
                </div>
                
                @if ($equipmentReport->image)
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Foto Kerusakan</h3>
                    <div class="mb-6">
                        <img src="{{ asset('storage/' . $equipmentReport->image) }}" alt="Foto Kerusakan" class="rounded-md max-w-full h-auto">
                    </div>
                @endif
                
                @if ($equipmentReport->notes)
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2">Catatan Admin</h3>
                    <div class="bg-yellow-50 p-4 rounded-md">
                        <p>{{ $equipmentReport->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
        
        @if (auth()->user()->role === 'admin' && $equipmentReport->status !== 'resolved')
            <div class="mt-8 pt-6 border-t">
                <h3 class="text-lg font-semibold mb-4">Tindakan Admin</h3>
                
                <form action="{{ route('equipment-reports.update', $equipmentReport) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="status" class="block text-gray-700 font-bold mb-2">Update Status</label>
                        <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pending" {{ $equipmentReport->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in-review" {{ $equipmentReport->status === 'in-review' ? 'selected' : '' }}>Sedang Ditinjau</option>
                            <option value="confirmed" {{ $equipmentReport->status === 'confirmed' ? 'selected' : '' }}>Konfirmasi & Buat Maintenance</option>
                            <option value="rejected" {{ $equipmentReport->status === 'rejected' ? 'selected' : '' }}>Tolak</option>
                            <option value="resolved" {{ $equipmentReport->status === 'resolved' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="block text-gray-700 font-bold mb-2">Catatan Admin</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $equipmentReport->notes }}</textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection 