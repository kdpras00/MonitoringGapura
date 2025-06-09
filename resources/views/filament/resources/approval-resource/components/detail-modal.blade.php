@php
    use Carbon\Carbon;
@endphp

<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <span class="font-medium text-gray-500">Peralatan</span>
            <p class="mt-1">{{ $record->equipment ? $record->equipment->name : $record->equipment_name ?? '-' }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-500">Tanggal Selesai</span>
            <p class="mt-1">{{ $record->actual_date ? Carbon::parse($record->actual_date)->format('d M Y H:i') : '-' }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-500">Teknisi</span>
            <p class="mt-1">{{ $record->technician ? $record->technician->name : '-' }}</p>
        </div>
        <div>
            <span class="font-medium text-gray-500">Status</span>
            <p class="mt-1">
                @if($record->status == 'completed')
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Selesai</span>
                @else
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ ucfirst($record->status) }}</span>
                @endif
            </p>
        </div>
        <div>
            <span class="font-medium text-gray-500">Hasil</span>
            <p class="mt-1">
                @if($record->result == 'good')
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Baik</span>
                @elseif($record->result == 'partial')
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Sebagian</span>
                @elseif($record->result == 'failed')
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Gagal</span>
                @else
                    <span>{{ $record->result ?? '-' }}</span>
                @endif
            </p>
        </div>
        <div>
            <span class="font-medium text-gray-500">Status Approval</span>
            <p class="mt-1">
                @if($record->approval_status == 'pending')
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Menunggu</span>
                @elseif($record->approval_status == 'approved')
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Disetujui</span>
                @elseif($record->approval_status == 'rejected')
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Ditolak</span>
                @else
                    <span>{{ $record->approval_status ?? '-' }}</span>
                @endif
            </p>
        </div>
    </div>

    <div>
        <span class="font-medium text-gray-500">Catatan Maintenance</span>
        <p class="mt-1">{{ $record->notes ?? '-' }}</p>
    </div>

    <div>
        <span class="font-medium text-gray-500">Catatan Approval</span>
        <p class="mt-1">{{ $record->approval_notes ?? '-' }}</p>
    </div>

    @if($record->approval_status == 'pending')
        <div class="flex space-x-2 mt-4">
            <button
                x-data="{}"
                x-on:click="$dispatch('open-modal', { id: 'approve-maintenance-{{ $record->id }}' })"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
                Setujui
            </button>
            <button
                x-data="{}"
                x-on:click="$dispatch('open-modal', { id: 'reject-maintenance-{{ $record->id }}' })"
                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
                Tolak
            </button>
        </div>
    @endif
</div> 