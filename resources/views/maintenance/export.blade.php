@extends('layouts.app')

@section('title', 'Export Laporan Maintenance')

@section('content')
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Export Laporan Maintenance</h1>
        <a href="{{ route('maintenance.supervisor') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
    </div>

    <!-- Export Options Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Opsi Ekspor</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('maintenance.generate-export') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">Tanggal Selesai</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                        <div class="form-group">
                            <label for="equipment_id">Equipment (Opsional)</label>
                            <select class="form-control" id="equipment_id" name="equipment_id">
                                <option value="">Semua Equipment</option>
                                @foreach($equipmentList as $equipment)
                                <option value="{{ $equipment->id }}">{{ $equipment->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status (Opsional)</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">Semua Status</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                                <option value="pending">Menunggu</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="export_format">Format Export</label>
                            <select class="form-control" id="export_format" name="export_format" required>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Informasi Yang Disertakan</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_photos" name="include_options[]" value="photos" checked>
                                <label class="form-check-label" for="include_photos">Foto Dokumentasi</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_checklist" name="include_options[]" value="checklist" checked>
                                <label class="form-check-label" for="include_checklist">Checklist Digital</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_location" name="include_options[]" value="location" checked>
                                <label class="form-check-label" for="include_location">Data Lokasi</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_notes" name="include_options[]" value="notes" checked>
                                <label class="form-check-label" for="include_notes">Catatan</label>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-file-export"></i> Generate Laporan
                </button>
            </form>
        </div>
    </div>

    <!-- Data Preview Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Preview Data Maintenance (5 Tahun Terakhir)</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Equipment</th>
                            <th>Tanggal</th>
                            <th>Teknisi</th>
                            <th>Status</th>
                            <th>Disetujui Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($maintenanceHistory as $index => $history)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $history->equipment_name }}</td>
                            <td>{{ $history->completion_date }}</td>
                            <td>{{ $history->technician }}</td>
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
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data maintenance</td>
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
        // Set tanggal default (1 bulan terakhir)
        var today = new Date();
        var lastMonth = new Date();
        lastMonth.setMonth(today.getMonth() - 1);
        
        $('#end_date').val(today.toISOString().substr(0, 10));
        $('#start_date').val(lastMonth.toISOString().substr(0, 10));
        
        // Init datatable
        $('#dataTable').DataTable({
            "order": [[2, "desc"]],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            }
        });
    });
</script>
@endsection 