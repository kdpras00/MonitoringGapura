<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses QR Langsung</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-center mb-6">Akses QR Code Langsung</h1>
        
        <div class="space-y-6">
            <div class="p-4 bg-blue-50 rounded-lg">
                <h2 class="text-lg font-semibold mb-3">Kode-kode QR Test:</h2>
                <div class="space-y-2">
                    <a href="{{ url('/q/TEST-123') }}" class="block bg-white p-3 rounded border border-blue-200 hover:bg-blue-100">
                        TEST-123
                    </a>
                    <a href="{{ url('/q/CVB-2023-001') }}" class="block bg-white p-3 rounded border border-blue-200 hover:bg-blue-100">
                        CVB-2023-001
                    </a>
                    <a href="{{ url('/q/BGC-2022-001') }}" class="block bg-white p-3 rounded border border-blue-200 hover:bg-blue-100">
                        BGC-2022-001 (will find similar codes)
                    </a>
                    <a href="{{ url('/q/' . urlencode('ABC DEF 2023')) }}" class="block bg-white p-3 rounded border border-blue-200 hover:bg-blue-100">
                        ABC DEF 2023 (dengan spasi)
                    </a>
                    <a href="{{ url('/q/' . urlencode('ABC+DEF 2023')) }}" class="block bg-white p-3 rounded border border-blue-200 hover:bg-blue-100">
                        ABC+DEF 2023 (dengan tanda +)
                    </a>
                </div>
            </div>
            
            <div class="p-4 bg-yellow-50 rounded-lg">
                <p class="text-yellow-800">
                    Klik pada kode QR di atas untuk langsung mengakses halaman detail peralatan.
                    Jika halaman tidak ditemukan (404), periksa apakah kode QR tersebut ada di database.
                </p>
            </div>
            
            <div class="mt-6 flex justify-between">
                <a href="{{ url('/url-test') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Halaman Test URL
                </a>
                <a href="{{ url('/qr-test') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Halaman Test QR
                </a>
            </div>
        </div>
    </div>
</body>
</html> 