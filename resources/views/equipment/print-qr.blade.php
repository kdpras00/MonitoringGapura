<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR Code - {{ $equipment->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
            .print-container {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header with print button -->
        <div class="no-print bg-white p-4 mb-8 rounded-lg shadow flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Print QR Code</h1>
            <div class="space-x-2">
                <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    Print QR Code
                </button>
                <a href="{{ url()->previous() }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                    Back
                </a>
            </div>
        </div>
        
        <!-- Print container -->
        <div class="print-container bg-white p-6 rounded-lg shadow-lg">
            <div class="text-center mb-2">
                <!-- QR Code Display -->
                <div class="mx-auto" style="width: 300px; height: 300px;">
                    {!! QrCode::size(200)->generate(url('/q/' . urlencode($equipment->qr_code))) !!}
                </div>
                
                <div class="mt-4">
                    <h2 class="text-xl font-bold">{{ $equipment->name }}</h2>
                    <p class="text-lg font-medium text-gray-700">{{ $equipment->qr_code }}</p>
                </div>
                
                <div class="mt-2 text-gray-600 text-sm">
                    <p>Serial: {{ $equipment->serial_number }}</p>
                    <p>Location: {{ $equipment->location }}</p>
                </div>
                
                <div class="mt-2 text-gray-500 text-xs">
                    <p>Scan with your phone camera or QR scanner app</p>
                    <p class="font-medium">{{ url('/q/' . urlencode($equipment->qr_code)) }}</p>
                    <p class="mt-1">Serial number access: <a href="{{ url('/equipment/serial/' . urlencode($equipment->serial_number)) }}" class="text-blue-600 hover:underline">{{ $equipment->serial_number }}</a></p>
                </div>
            </div>
        </div>
        
        <!-- Debug Information - won't be printed -->
        <div class="no-print mt-8 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <h3 class="text-lg font-semibold text-yellow-800 mb-2">Debug Information:</h3>
            <ul class="list-disc pl-5 text-yellow-700">
                <li>QR Code Contents: <code>{{ url('/q/' . urlencode($equipment->qr_code)) }}</code></li>
                <li>Equipment QR Code: <code>{{ $equipment->qr_code }}</code></li>
                <li>Host: <code>{{ request()->getHost() }}:{{ request()->getPort() }}</code></li>
                <li>URL encoded QR: <code>{{ urlencode($equipment->qr_code) }}</code></li>
            </ul>
        </div>
        
        <!-- Instructions - won't be printed -->
        <div class="no-print mt-8 bg-blue-50 p-4 rounded-lg border border-blue-200">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">Printing Instructions:</h3>
            <ul class="list-disc pl-5 text-blue-700">
                <li>Click the "Print QR Code" button above</li>
                <li>Use high-quality paper for better scanning</li>
                <li>Consider using sticker paper if available</li>
                <li>Ensure printer resolution is set to high for clear QR codes</li>
                <li>Print multiple copies if needed</li>
            </ul>
        </div>
    </div>
</body>
</html> 