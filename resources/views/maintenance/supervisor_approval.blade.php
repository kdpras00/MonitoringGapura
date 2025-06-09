@extends('layouts.app')

@section('title', 'Approval Maintenance')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Approval Maintenance</h1>
        <a href="{{ route('maintenance.export-reports') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Export Laporan
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Maintenance Waiting For Approval -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Menunggu Persetujuan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="pendingTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Equipment</th>
                            <th>Teknisi</th>
                            <th>Tanggal Selesai</th>
                            <th>Lokasi</th>
                            <th>Durasi</th>
                            <th>Hasil</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingApprovals as $maintenance)
                        <tr>
                            <td>{{ $maintenance->equipment_name }}</td>
                            <td>{{ $maintenance->technician }}</td>
                            <td>{{ $maintenance->completion_date }}</td>
                            <td>
                                <span class="badge badge-info" data-toggle="tooltip" data-html="true" 
                                    title="Lat: {{ $maintenance->location_lat }}<br>Long: {{ $maintenance->location_lng }}<br>Time: {{ $maintenance->location_timestamp }}">
                                    <i class="fas fa-map-marker-alt"></i> Terverifikasi
                                </span>
                            </td>
                            <td>{{ $maintenance->duration }} menit</td>
                            <td>
                                @if($maintenance->result == 'good')
                                <span class="badge badge-success">Baik</span>
                                @elseif($maintenance->result == 'partial')
                                <span class="badge badge-warning">Sebagian</span>
                                @else
                                <span class="badge badge-danger">Gagal</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('maintenance.approval.detail', $maintenance->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data yang menunggu persetujuan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Maintenance Approval History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Riwayat Persetujuan (5 Tahun Terakhir)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="historyTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Equipment</th>
                            <th>Teknisi</th>
                            <th>Tanggal Maintenance</th>
                            <th>Status</th>
                            <th>Disetujui Oleh</th>
                            <th>Tanggal Persetujuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvalHistory as $history)
                        <tr>
                            <td>{{ $history->equipment_name }}</td>
                            <td>{{ $history->technician }}</td>
                            <td>{{ $history->completion_date }}</td>
                            <td>
                                @if($history->approval_status == 'approved')
                                <span class="badge badge-success">Disetujui</span>
                                @elseif($history->approval_status == 'rejected')
                                <span class="badge badge-danger">Ditolak</span>
                                @else
                                <span class="badge badge-warning">Menunggu</span>
                                @endif
                            </td>
                            <td>{{ $history->approved_by ?? '-' }}</td>
                            <td>{{ $history->approval_date ?? '-' }}</td>
                            <td>
                                <a href="{{ route('maintenance.history.detail', $history->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data riwayat persetujuan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#pendingTable').DataTable({
            "order": [[2, "desc"]],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            }
        });
        
        $('#historyTable').DataTable({
            "order": [[5, "desc"]],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            }
        });
        
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
@endsection 