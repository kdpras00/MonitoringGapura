<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Scan {{ $equipment->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
        }
        .scan-container {
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
        .qr-img {
            margin: 15px auto;
            max-width: 200px;
            height: auto;
        }
        .equipment-info {
            margin-bottom: 15px;
        }
        .scan-method {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        .scan-title {
            font-weight: 600;
            margin-bottom: 10px;
            color: #4c6ef5;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
            .scan-container {
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
                <a href="{{ route('filament.admin.resources.equipment.index') }}" class="btn btn-secondary no-print">&laquo; Kembali</a>
                <button onclick="window.print()" class="btn btn-primary no-print">Cetak Kode Scan</button>
            </div>
        </div>
        
        <div class="scan-container">
            <h4>{{ $equipment->name }}</h4>
            <div class="equipment-info">
                <strong>Nomor Seri:</strong> {{ $equipment->serial_number }}<br>
                <strong>Kode Scan:</strong> {{ $equipment->barcode ?: $equipment->qr_code }}<br>
                <strong>Lokasi:</strong> {{ $equipment->location }}
            </div>
            
            <div class="scan-method">
                <p class="scan-title">Scan QR Code dengan kamera smartphone</p>
                <div class="qr-img">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode(url('/equipment/view/' . $equipment->id)) }}" 
                         alt="QR Code" class="img-fluid">
                </div>
            </div>
            
            <div class="scan-method">
                <p class="scan-title">Atau scan barcode dengan aplikasi scanner</p>
                <div class="barcode-img">
                    {!! $equipment->getHtmlBarcode() !!}
                </div>
            </div>
            
            <p class="small text-muted mt-3">Scan kode di atas untuk melihat informasi detail peralatan</p>
            
            <p class="small mt-4">
                <a href="{{ url('/equipment/view/' . $equipment->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    Lihat Detail Equipment
                </a>
                
                <!-- Opsi alternatif -->
                <a href="http://127.0.0.1:8000/equipment/view/{{ $equipment->id }}" target="_blank" class="btn btn-sm btn-outline-success mt-2">
                    Alternatif: Lihat Detail Equipment
                </a>
            </p>
        </div>
    </div>
</body>
</html> 