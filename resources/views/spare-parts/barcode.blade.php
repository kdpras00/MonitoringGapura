<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode {{ $sparePart->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
        }
        .barcode-container {
            text-align: center;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: white;
        }
        .barcode-img {
            margin: 15px 0;
            max-width: 100%;
        }
        .part-info {
            margin-bottom: 15px;
        }
        .btn-print {
            margin-top: 15px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
            .barcode-container {
                border: none;
                padding: 0;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <a href="{{ route('filament.admin.resources.spare-parts.index') }}" class="btn btn-secondary no-print">&laquo; Kembali</a>
                <button onclick="window.print()" class="btn btn-primary no-print">Cetak Barcode</button>
            </div>
        </div>
        
        <div class="barcode-container">
            <h4>{{ $sparePart->name }}</h4>
            <div class="part-info">
                <strong>Nomor Part:</strong> {{ $sparePart->part_number }}<br>
                <strong>Barcode:</strong> {{ $sparePart->barcode }}
            </div>
            
            <div class="barcode-img">
                {!! $sparePart->getHtmlBarcode() !!}
            </div>
            
            <p class="small text-muted">Scan barcode untuk melihat informasi detail</p>
            
            <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('spare-parts.scan', ['code' => $sparePart->barcode])) }}" 
                     alt="QR Code">
            </div>
            
            <p class="small mt-2">
                <a href="{{ route('spare-parts.scan', ['code' => $sparePart->barcode]) }}" target="_blank">
                    {{ route('spare-parts.scan', ['code' => $sparePart->barcode]) }}
                </a>
            </p>
        </div>
    </div>
</body>
</html> 