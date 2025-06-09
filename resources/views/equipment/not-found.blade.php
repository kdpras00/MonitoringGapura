<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Tidak Ditemukan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .error-container {
            text-align: center;
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        .error-icon {
            font-size: 60px;
            color: #d9534f;
            margin-bottom: 20px;
        }
        .error-code {
            background-color: #f8f9fa;
            padding: 8px 15px;
            border-radius: 4px;
            font-family: monospace;
            margin: 15px auto;
            max-width: 250px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                </svg>
            </div>
            
            <h2 class="mb-3">Barcode Equipment Tidak Ditemukan</h2>
            
            <p class="text-muted">
                Maaf, barcode atau kode yang Anda scan tidak terdaftar dalam sistem.
            </p>
            
            @if(isset($code) && !empty($code))
                <div class="error-code">
                    {{ $code }}
                </div>
            @endif
            
            <p class="small mt-4">
                Silahkan pastikan Anda memindai barcode equipment yang benar.
                <br>
                Jika Anda yakin barcode ini seharusnya terdaftar, mohon hubungi administrator.
            </p>
            
            <div class="mt-4">
                <a href="/" class="btn btn-primary">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html> 