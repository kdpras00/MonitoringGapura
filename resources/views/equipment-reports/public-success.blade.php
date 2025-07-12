<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Berhasil - MonitoringGapura</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-16">
        <div class="max-w-lg mx-auto bg-white rounded-lg shadow-md p-8">
            <div class="text-center">
                <svg class="mx-auto h-16 w-16 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                
                <h1 class="mt-4 text-2xl font-bold text-gray-800">Laporan Kerusakan Berhasil Dikirim!</h1>
                
                <p class="mt-2 text-gray-600">
                    Terima kasih telah melaporkan kerusakan peralatan. Laporan Anda telah diterima dan akan ditinjau oleh tim maintenance.
                </p>
                
                <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-medium text-gray-700">Detail Laporan:</h3>
                    <p class="text-gray-600">ID Laporan: {{ session('report') ? session('report')->id : '-' }}</p>
                    <p class="text-gray-600">Tanggal Laporan: {{ session('report') ? session('report')->reported_at->format('d M Y, H:i') : date('d M Y, H:i') }}</p>
                    <p class="text-gray-600">Status: Menunggu Persetujuan</p>
                </div>
                
                <div class="mt-8">
                    <a href="{{ route('public.report.create') }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-md">
                        Buat Laporan Lain
                    </a>
                    
                    <a href="/" class="inline-block ml-4 text-gray-600 hover:text-gray-800">
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 