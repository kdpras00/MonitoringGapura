<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Spare Part - {{ $sparePart->name }}</title>
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
        .part-info {
            margin-bottom: 10px;
        }
        .part-info strong {
            display: inline-block;
            min-width: 120px;
        }
        .stock-available {
            color: #1e8a49;
            font-weight: bold;
        }
        .stock-low {
            color: #e67700;
            font-weight: bold;
        }
        .stock-out {
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
                        <h4 class="mb-0">Detail Spare Part</h4>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-4">{{ $sparePart->name }}</h5>
                        
                        <div class="part-info">
                            <strong>Nomor Part:</strong> {{ $sparePart->part_number }}
                        </div>
                        
                        <div class="part-info">
                            <strong>Barcode:</strong> {{ $sparePart->barcode }}
                        </div>
                        
                        <div class="part-info">
                            <strong>Jumlah Stok:</strong> 
                            @if($sparePart->status == 'available')
                                <span class="stock-available">{{ $sparePart->stock }} (Tersedia)</span>
                            @elseif($sparePart->status == 'low_stock')
                                <span class="stock-low">{{ $sparePart->stock }} (Stok Rendah)</span>
                            @else
                                <span class="stock-out">{{ $sparePart->stock }} (Habis)</span>
                            @endif
                        </div>
                        
                        <div class="part-info">
                            <strong>Harga:</strong> Rp {{ number_format($sparePart->price, 0, ',', '.') }}
                        </div>
                        
                        <hr>
                        
                        <div class="text-center mt-4">
                            <img src="{{ $sparePart->getPngBarcode() }}" alt="Barcode" class="img-fluid mb-2" style="max-width: 200px;">
                            <p class="small text-muted mb-0">{{ $sparePart->barcode }}</p>
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