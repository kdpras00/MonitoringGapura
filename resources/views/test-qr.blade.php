<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-center mb-6">QR Code Test</h1>
        
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-3">Test 1: Direct QR Code</h2>
            <div class="mx-auto mb-2" style="width: 200px; height: 200px;">
                {!! QrCode::size(200)->generate(url('/q/TEST-123')) !!}
            </div>
            <p class="text-center">URL: <code>{{ url('/q/TEST-123') }}</code></p>
        </div>
        
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-3">Test 2: QR Code with Special Characters</h2>
            <div class="mx-auto mb-2" style="width: 200px; height: 200px;">
                {!! QrCode::size(200)->generate(url('/q/' . urlencode('ABC+DEF 2023'))) !!}
            </div>
            <p class="text-center">URL: <code>{{ url('/q/' . urlencode('ABC+DEF 2023')) }}</code></p>
            <p class="text-center">Raw code: <code>ABC+DEF 2023</code></p>
        </div>
        
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="font-semibold text-yellow-800 mb-2">How to test:</h3>
            <ol class="list-decimal pl-5 text-yellow-700">
                <li>Scan each QR code with your phone</li>
                <li>They should lead to your application at /q/TEST-123 and /q/ABC+DEF%202023</li>
                <li>The controller should decode these values properly</li>
            </ol>
        </div>
    </div>
</body>
</html> 