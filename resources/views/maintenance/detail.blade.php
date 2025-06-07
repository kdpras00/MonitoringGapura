@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Debug Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Debug Info</h6>
                </div>
                <div class="card-body">
                    @if(isset($equipmentData))
                        <p>Data tersedia: Equipment ID = {{ $equipmentData['id'] ?? 'tidak ada' }}</p>
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
            <a href="{{ route('maintenance.index') }}" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <h1 class="h3 mb-0 text-gray-800">Detail Peralatan: {{ $equipmentData['name'] ?? 'Tidak ada nama' }}</h1>
            <p class="text-muted">{{ $equipmentData['id'] ?? 'ID tidak tersedia' }} - {{ $equipmentData['location'] ?? 'Lokasi tidak tersedia' }}</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Skor Kondisi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $equipmentData['condition_score'] ?? 'N/A' }}/100</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tachometer-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Maintenance Terakhir</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $equipmentData['last_maintenance'] ?? 'Tidak ada data' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Maintenance Berikutnya</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $equipmentData['next_maintenance'] ?? 'Tidak ada data' }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Rekomendasi</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if(isset($equipmentData['prediction']['urgency_level']))
                                    {{ ucfirst($equipmentData['prediction']['urgency_level']) }}
                                @else
                                    Normal
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Data Sensor Terkini</h6>
                </div>
                <div class="card-body">
                    @if(isset($equipmentData['sensor_data']))
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Parameter</th>
                                        <th>Nilai</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Vibrasi</td>
                                        <td>{{ $equipmentData['sensor_data']['vibration'] ?? 'N/A' }} mm/s</td>
                                        <td>
                                            @if(isset($equipmentData['sensor_data']['vibration']))
                                                @if($equipmentData['sensor_data']['vibration'] > 6)
                                                    <span class="badge badge-danger">Tinggi</span>
                                                @elseif($equipmentData['sensor_data']['vibration'] > 5)
                                                    <span class="badge badge-warning">Perhatian</span>
                                                @else
                                                    <span class="badge badge-success">Normal</span>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Tidak ada data</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Temperatur</td>
                                        <td>{{ $equipmentData['sensor_data']['temperature'] ?? 'N/A' }} °C</td>
                                        <td>
                                            @if(isset($equipmentData['sensor_data']['temperature']))
                                                @if($equipmentData['sensor_data']['temperature'] > 90)
                                                    <span class="badge badge-danger">Tinggi</span>
                                                @elseif($equipmentData['sensor_data']['temperature'] > 80)
                                                    <span class="badge badge-warning">Perhatian</span>
                                                @else
                                                    <span class="badge badge-success">Normal</span>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Tidak ada data</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Tekanan</td>
                                        <td>{{ $equipmentData['sensor_data']['pressure'] ?? 'N/A' }} PSI</td>
                                        <td>
                                            @if(isset($equipmentData['sensor_data']['pressure']))
                                                @if($equipmentData['sensor_data']['pressure'] > 130)
                                                    <span class="badge badge-danger">Tinggi</span>
                                                @elseif($equipmentData['sensor_data']['pressure'] > 120)
                                                    <span class="badge badge-warning">Perhatian</span>
                                                @else
                                                    <span class="badge badge-success">Normal</span>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Tidak ada data</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Kelembaban</td>
                                        <td>{{ $equipmentData['sensor_data']['humidity'] ?? 'N/A' }} %</td>
                                        <td>
                                            @if(isset($equipmentData['sensor_data']['humidity']))
                                                @if($equipmentData['sensor_data']['humidity'] > 70)
                                                    <span class="badge badge-danger">Tinggi</span>
                                                @elseif($equipmentData['sensor_data']['humidity'] > 60)
                                                    <span class="badge badge-warning">Perhatian</span>
                                                @else
                                                    <span class="badge badge-success">Normal</span>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Tidak ada data</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">Data sensor tidak tersedia</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Hasil Prediksi AI</h6>
                </div>
                <div class="card-body">
                    @if(isset($equipmentData['prediction']))
                        <div class="alert alert-{{ isset($equipmentData['prediction']['maintenance_required']) && $equipmentData['prediction']['maintenance_required'] ? 'warning' : 'success' }}">
                            <strong>Status:</strong> 
                            {{ isset($equipmentData['prediction']['maintenance_required']) && $equipmentData['prediction']['maintenance_required'] ? 'Maintenance diperlukan' : 'Tidak perlu maintenance' }}
                        </div>

                        @if(isset($equipmentData['prediction']['maintenance_required']) && $equipmentData['prediction']['maintenance_required'])
                            <h5 class="font-weight-bold">Rekomendasi:</h5>
                            <p>{{ $equipmentData['recommendation'] ?? 'Tidak ada rekomendasi' }}</p>

                            @if(isset($equipmentData['prediction']['potential_issues']) && count($equipmentData['prediction']['potential_issues']) > 0)
                                <h5 class="font-weight-bold">Potensi Masalah:</h5>
                                <ul>
                                    @foreach($equipmentData['prediction']['potential_issues'] as $issue)
                                        <li>{{ $issue }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if(isset($equipmentData['prediction']['parts_needed']) && count($equipmentData['prediction']['parts_needed']) > 0)
                                <h5 class="font-weight-bold">Suku Cadang yang Diperlukan:</h5>
                                <ul>
                                    @foreach($equipmentData['prediction']['parts_needed'] as $part)
                                        <li>{{ $part }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if(isset($equipmentData['prediction']['estimated_maintenance_time_hours']))
                                <p><strong>Estimasi Waktu Maintenance:</strong> 
                                {{ $equipmentData['prediction']['estimated_maintenance_time_hours'] }} jam</p>
                            @endif

                            <div class="mt-4">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#scheduleModal">
                                    Jadwalkan Maintenance
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            Data prediksi tidak tersedia.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(isset($equipmentData['prediction']['justification']))
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Justifikasi AI</h6>
                </div>
                <div class="card-body">
                    <p>{{ $equipmentData['prediction']['justification'] }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Jadwalkan Maintenance -->
<div class="modal fade" id="scheduleModal" tabindex="-1" role="dialog" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('maintenance.schedule', $equipmentData['id'] ?? '') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleModalLabel">Jadwalkan Maintenance</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="scheduled_date">Tanggal Maintenance</label>
                        <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" required>
                    </div>
                    <div class="form-group">
                        <label for="technician">Teknisi</label>
                        <input type="text" class="form-control" id="technician" name="technician" required>
                    </div>
                    <div class="form-group">
                        <label for="notes">Catatan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 