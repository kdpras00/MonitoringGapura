<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-center mb-6">URL Tester</h1>
        
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
            <h2 class="text-lg font-semibold mb-2">Klik link berikut untuk menguji URL</h2>
            
            <div class="space-y-3">
                <div>
                    <h3 class="font-medium">Test 1: Kode Sederhana</h3>
                    <a href="{{ url('/q/TEST-123') }}" class="text-blue-600 hover:underline">
                        {{ url('/q/TEST-123') }}
                    </a>
                </div>
                
                <div>
                    <h3 class="font-medium">Test 2: Kode dengan Spasi</h3>
                    <a href="{{ url('/q/' . urlencode('ABC DEF 2023')) }}" class="text-blue-600 hover:underline">
                        {{ url('/q/' . urlencode('ABC DEF 2023')) }}
                    </a>
                </div>
                
                <div>
                    <h3 class="font-medium">Test 3: Kode dengan Plus (+)</h3>
                    <a href="{{ url('/q/' . urlencode('ABC+DEF 2023')) }}" class="text-blue-600 hover:underline">
                        {{ url('/q/' . urlencode('ABC+DEF 2023')) }}
                    </a>
                </div>
                
                <div>
                    <h3 class="font-medium">Test 4: Kode dengan Karakter Khusus</h3>
                    <a href="{{ url('/q/' . urlencode('XYZ@123#456')) }}" class="text-blue-600 hover:underline">
                        {{ url('/q/' . urlencode('XYZ@123#456')) }}
                    </a>
                </div>
            </div>
        </div>
        
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Testing Manual</h2>
            <form id="testForm" class="space-y-4">
                <div>
                    <label for="qrCode" class="block text-sm font-medium text-gray-700">Kode QR untuk Diuji:</label>
                    <input type="text" id="qrCode" name="qrCode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" 
                           placeholder="Masukkan kode QR (contoh: ABC+DEF 2023)">
                </div>
                
                <div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                        Uji URL
                    </button>
                </div>
                
                <div id="result" class="p-3 bg-gray-100 rounded-lg hidden">
                    <p class="font-medium">URL yang Dihasilkan:</p>
                    <a id="resultUrl" href="#" class="text-blue-600 hover:underline block mt-1 break-all"></a>
                </div>
            </form>
        </div>
        
        <div class="p-4 bg-yellow-50 rounded-lg">
            <h3 class="font-semibold text-yellow-800 mb-2">Informasi Debug:</h3>
            <p>Pada pengkodean URL:</p>
            <ul class="list-disc pl-5 text-sm text-yellow-700 space-y-1">
                <li>Spasi diubah menjadi <code>%20</code></li>
                <li>Tanda + diubah menjadi <code>%2B</code></li>
                <li>Karakter # diubah menjadi <code>%23</code></li>
                <li>Tanda @ diubah menjadi <code>%40</code></li>
            </ul>
        </div>
    </div>
    
    <script>
        document.getElementById('testForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const qrCode = document.getElementById('qrCode').value;
            const encodedCode = encodeURIComponent(qrCode);
            const baseUrl = "{{ url('/q') }}";
            const fullUrl = `${baseUrl}/${encodedCode}`;
            
            const resultDiv = document.getElementById('result');
            const resultUrl = document.getElementById('resultUrl');
            
            resultUrl.href = fullUrl;
            resultUrl.textContent = fullUrl;
            resultDiv.classList.remove('hidden');
        });
    </script>
</body>
</html> 