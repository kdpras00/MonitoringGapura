@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Daftar Laporan Kerusakan</h2>
        </div>
        
        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                {{ session('success') }}
            </div>
        @endif
        
        @if (count($reports) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">ID</th>
                            <th class="py-3 px-6 text-left">Equipment</th>
                            <th class="py-3 px-6 text-left">Pelapor</th>
                            <th class="py-3 px-6 text-left">Urgensi</th>
                            <th class="py-3 px-6 text-left">Status</th>
                            <th class="py-3 px-6 text-left">Tanggal Laporan</th>
                            <th class="py-3 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        @foreach ($reports as $report)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6 text-left">{{ $report->id }}</td>
                                <td class="py-3 px-6 text-left">{{ $report->equipment->name ?? 'N/A' }}</td>
                                <td class="py-3 px-6 text-left">{{ $report->reporter->name ?? 'N/A' }}</td>
                                <td class="py-3 px-6 text-left">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                        {{ $report->urgency_level === 'high' ? 'bg-red-100 text-red-800' : 
                                           ($report->urgency_level === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ $report->urgency_level === 'high' ? 'Tinggi' : 
                                           ($report->urgency_level === 'medium' ? 'Sedang' : 'Rendah') }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                        {{ $report->status === 'pending' ? 'bg-gray-100 text-gray-800' : 
                                           ($report->status === 'in-review' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($report->status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                           ($report->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'))) }}">
                                        {{ $report->status === 'pending' ? 'Pending' : 
                                           ($report->status === 'in-review' ? 'Sedang Ditinjau' : 
                                           ($report->status === 'confirmed' ? 'Dikonfirmasi' : 
                                           ($report->status === 'rejected' ? 'Ditolak' : 'Selesai'))) }}
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-left">{{ $report->reported_at->format('d M Y H:i') }}</td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center">
                                        <a href="{{ route('equipment-reports.show', $report) }}" class="w-4 mr-2 transform hover:text-blue-500 hover:scale-110">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if ($report->reporter_id === auth()->id() && $report->status === 'pending')
                                            <a href="{{ route('equipment-reports.edit', $report) }}" class="w-4 mr-2 transform hover:text-yellow-500 hover:scale-110">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('equipment-reports.destroy', $report) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-4 transform hover:text-red-500 hover:scale-110" onclick="return confirm('Apakah Anda yakin ingin menghapus laporan ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $reports->links() }}
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-gray-600">Belum ada laporan kerusakan.</p>
            </div>
        @endif
    </div>
</div>
@endsection 