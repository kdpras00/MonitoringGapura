@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Dashboard Predictive Maintenance</h1>
        </div>
    </div>

    <!-- Debug Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Debug Info</h6>
                </div>
                <div class="card-body">
                    @if(isset($equipmentData))
                        <p>Data tersedia: {{ count($equipmentData) }} item</p>
                        <p>Tipe data: {{ gettype($equipmentData) }}</p>
                    @else
                        <p>Data tidak tersedia (null)</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Status Peralatan</h6>
                    <div class="dropdown no-arrow">
                        <a href="{{ route('maintenance.refresh') }}" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-sync fa-sm text-white-50"></i> Refresh Data
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th>Maintenance Terakhir</th>
                                    <th>Prediksi Maintenance Berikutnya</th>
                                    <th>Skor Kondisi</th>
                                    <th>Rekomendasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($equipmentData) && is_array($equipmentData) && count($equipmentData) > 0)
                                    @foreach($equipmentData as $equipment)
                                    <tr>
                                        <td>{{ $equipment['name'] ?? 'Tidak ada nama' }}</td>
                                        <td>{{ $equipment['last_maintenance'] ?? 'Tidak ada data' }}</td>
                                        <td>
                                            @if(isset($equipment['next_maintenance']) && $equipment['next_maintenance'] == 'Tidak diperlukan')
                                                <span class="badge badge-success">{{ $equipment['next_maintenance'] }}</span>
                                            @elseif(isset($equipment['next_maintenance']))
                                                <span class="badge badge-warning">{{ $equipment['next_maintenance'] }}</span>
                                            @else
                                                <span class="badge badge-secondary">Tidak ada data</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($equipment['condition_score']))
                                                @php
                                                    $scoreClass = 'danger';
                                                    if ($equipment['condition_score'] >= 85) {
                                                        $scoreClass = 'success';
                                                    } elseif ($equipment['condition_score'] >= 70) {
                                                        $scoreClass = 'info';
                                                    } elseif ($equipment['condition_score'] >= 50) {
                                                        $scoreClass = 'warning';
                                                    }
                                                @endphp
                                                <div class="progress">
                                                    <div class="progress-bar bg-{{ $scoreClass }}" role="progressbar" 
                                                        style="width: {{ $equipment['condition_score'] }}%" 
                                                        aria-valuenow="{{ $equipment['condition_score'] }}" 
                                                        aria-valuemin="0" aria-valuemax="100">
                                                        {{ $equipment['condition_score'] }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Tidak ada data</span>
                                            @endif
                                        </td>
                                        <td>{{ $equipment['recommendation'] ?? 'Tidak ada rekomendasi' }}</td>
                                        <td>
                                            @if(isset($equipment['id']))
                                                <a href="{{ route('maintenance.show', $equipment['id']) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-secondary" disabled>Detail</button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data peralatan yang tersedia</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable();
    });
</script>
@endpush 