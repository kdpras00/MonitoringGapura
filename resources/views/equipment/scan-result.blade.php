<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Equipment - {{ $equipment->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #4c6ef5;
            color: white;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 15px 20px;
        }
        .card-body {
            padding: 20px;
        }
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        .equipment-info {
            margin-bottom: 10px;
        }
        .equipment-info strong {
            display: inline-block;
            min-width: 120px;
        }
        .status-active {
            color: #1e8a49;
            font-weight: bold;
        }
        .status-maintenance {
            color: #e67700;
            font-weight: bold;
        }
        .status-retired {
            color: #d9534f;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Detail Equipment</h4>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-4">{{ $equipment->name }}</h5>
                        
                        <div class="equipment-info">
                            <strong>Nomor Seri:</strong> {{ $equipment->serial_number }}
                        </div>
                        
                        <div class="equipment-info">
                            <strong>Barcode:</strong> {{ $equipment->barcode }}
                        </div>
                        
                        <div class="equipment-info">
                            <strong>Lokasi:</strong> {{ $equipment->location }}
                        </div>
                        
                        <div class="equipment-info">
                            <strong>Status:</strong> 
                            @if($equipment->status == 'active')
                                <span class="status-active">Active</span>
                            @elseif($equipment->status == 'maintenance')
                                <span class="status-maintenance">Under Maintenance</span>
                            @else
                                <span class="status-retired">Retired</span>
                            @endif
                        </div>
                        
                        <div class="equipment-info">
                            <strong>Instalasi:</strong> {{ $equipment->installation_date ? date('d M Y', strtotime($equipment->installation_date)) : '-' }}
                        </div>
                        
                        @if($equipment->specifications)
                        <div class="mt-4">
                            <strong>Spesifikasi:</strong>
                            <p class="mt-1">{{ $equipment->specifications }}</p>
                        </div>
                        @endif
                        
                        <hr>
                        
                        <div class="text-center mt-4">
                            <img src="{{ $equipment->getPngBarcode() }}" alt="Barcode" class="img-fluid mb-2" style="max-width: 200px;">
                            <p class="small text-muted mb-0">{{ $equipment->barcode ?: $equipment->qr_code }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="text-muted small">
                        Scan dilakukan pada: {{ now()->format('d M Y H:i:s') }}<br>
                        MonitoringGapura &copy; {{ date('Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 