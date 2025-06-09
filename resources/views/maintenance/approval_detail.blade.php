@extends('layouts.app')

@section('title', 'Detail Approval Maintenance')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Maintenance #{{ $maintenance->id }}</h1>
        <a href="{{ route('maintenance.supervisor') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <!-- Basic Info Card -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Maintenance</h6>
                    <div class="dropdown no-arrow">
                        <span class="badge badge-{{ $maintenance->result == 'good' ? 'success' : ($maintenance->result == 'partial' ? 'warning' : 'danger') }} p-2">
                            {{ $maintenance->result == 'good' ? 'Baik' : ($maintenance->result == 'partial' ? 'Sebagian' : 'Gagal') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Equipment:</strong></td>
                                    <td>{{ $maintenance->equipment_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Teknisi:</strong></td>
                                    <td>{{ $maintenance->technician }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Maintenance:</strong></td>
                                    <td>{{ $maintenance->completion_date }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Durasi:</strong></td>
                                    <td>{{ $maintenance->duration }} menit</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Alat:</strong></td>
                                    <td>{{ ucfirst($maintenance->equipment_type) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Prioritas:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $maintenance->priority == 'merah' ? 'danger' : ($maintenance->priority == 'kuning' ? 'warning' : 'success') }}">
                                            {{ ucfirst($maintenance->priority) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Lokasi Teknisi:</strong></td>
                                    <td>
                                        <span data-toggle="tooltip" data-html="true" 
                                            title="Lat: {{ $maintenance->location_lat }}<br>Long: {{ $maintenance->location_lng }}">
                                            <i class="fas fa-map-marker-alt text-danger"></i> 
                                            {{ $maintenance->location_lat }}, {{ $maintenance->location_lng }}
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $maintenance->location_timestamp }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Checklist Digital:</strong></td>
                                    <td>
                                        @php
                                            try {
                                                $checklist = !empty($maintenance->checklist) ? json_decode($maintenance->checklist, true) : [];
                                                if (!is_array($checklist)) {
                                                    $checklist = [];
                                                }
                                            } catch (\Exception $e) {
                                                $checklist = [];
                                            }
                                        @endphp
                                        @if(is_array($checklist) && count($checklist) > 0)
                                            @foreach($checklist as $check)
                                                <span class="badge badge-info mr-1">{{ is_array($check) ? ($check['step'] ?? '-') : $check }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Tidak ada data checklist</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Catatan:</strong></td>
                                    <td>{{ $maintenance->completion_notes }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photos Card -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Foto Dokumentasi</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 text-center">
                            <h5>Sebelum Maintenance</h5>
                            <img src="{{ asset('storage/maintenance/' . $maintenance->before_image) }}" alt="Sebelum" class="img-fluid mb-2" style="max-height: 300px;">
                            <p class="text-muted">{{ $maintenance->before_image_time }}</p>
                        </div>
                        <div class="col-md-6 text-center">
                            <h5>Setelah Maintenance</h5>
                            <img src="{{ asset('storage/maintenance/' . $maintenance->after_image) }}" alt="Setelah" class="img-fluid mb-2" style="max-height: 300px;">
                            <p class="text-muted">{{ $maintenance->after_image_time }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Form -->
    @if($maintenance->approval_status == 'pending')
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Formulir Persetujuan</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('maintenance.approve', $maintenance->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="approval_status">Status Persetujuan</label>
                            <select class="form-control" id="approval_status" name="approval_status" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="approval_notes">Catatan</label>
                            <textarea class="form-control" id="approval_notes" name="approval_notes" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Simpan Persetujuan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4 border-left-{{ $maintenance->approval_status == 'approved' ? 'success' : 'danger' }}">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-{{ $maintenance->approval_status == 'approved' ? 'success' : 'danger' }}">
                        Status Persetujuan
                    </h6>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> 
                        <span class="badge badge-{{ $maintenance->approval_status == 'approved' ? 'success' : 'danger' }}">
                            {{ $maintenance->approval_status == 'approved' ? 'Disetujui' : 'Ditolak' }}
                        </span>
                    </p>
                    <p><strong>Disetujui Oleh:</strong> {{ $maintenance->approved_by }}</p>
                    <p><strong>Tanggal Persetujuan:</strong> {{ $maintenance->approval_date }}</p>
                    <p><strong>Catatan:</strong> {{ $maintenance->approval_notes ?? 'Tidak ada catatan' }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection 