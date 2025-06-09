<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $equipment->name }} - Detail</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-md mx-auto bg-white shadow-lg rounded-lg overflow-hidden mt-10 mb-10">
        <div class="px-6 py-4">
            <div class="bg-blue-600 -mx-6 -mt-4 text-white text-center py-4 mb-4">
                <h1 class="text-xl font-bold">Airport Equipment Information</h1>
                <p class="text-sm text-blue-200">Scan code: {{ $equipment->qr_code }}</p>
            </div>
            
            <div class="flex items-center mb-6">
                <div class="bg-blue-100 p-3 rounded-full mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-800">{{ $equipment->name }}</h2>
            </div>
            
            <div class="space-y-3 mb-6">
                <div class="flex items-center text-gray-700">
                    <span class="font-semibold w-1/3">Serial Number:</span>
                    <span class="text-gray-600">{{ $equipment->serial_number }}</span>
                </div>
                
                <div class="flex items-center text-gray-700">
                    <span class="font-semibold w-1/3">Location:</span>
                    <span class="text-gray-600">{{ $equipment->location }}</span>
                </div>
                
                <div class="flex items-center text-gray-700">
                    <span class="font-semibold w-1/3">Installation:</span>
                    <span class="text-gray-600">{{ date('d M Y', strtotime($equipment->installation_date)) }}</span>
                </div>
                
                <div class="flex items-center text-gray-700">
                    <span class="font-semibold w-1/3">Status:</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                        {{ $equipment->status === 'active' ? 'bg-green-100 text-green-800' : 
                          ($equipment->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                        {{ ucfirst($equipment->status) }}
                    </span>
                </div>
            </div>
            
            <!-- Specifications -->
            <div class="border-t border-gray-200 pt-4 mt-4">
                <h3 class="text-lg font-semibold mb-2 text-gray-800">Specifications</h3>
                <p class="text-gray-600 text-sm">{{ $equipment->specifications }}</p>
            </div>
            
            <!-- Maintenance Checklist -->
            @if(isset($equipment->checklist_array) && is_array($equipment->checklist_array) && count($equipment->checklist_array) > 0)
            <div class="border-t border-gray-200 pt-4 mt-4">
                <h3 class="text-lg font-semibold mb-2 text-gray-800">Maintenance Checklist</h3>
                <ul class="list-disc pl-5 text-sm text-gray-600">
                    @foreach($equipment->checklist_array as $item)
                        <li>{{ is_array($item) && isset($item['step']) ? $item['step'] : $item }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <!-- Manual & SOP Links -->
            <div class="border-t border-gray-200 pt-4 mt-4 flex flex-col space-y-2">
                @if($equipment->manual_url)
                <a href="{{ asset('storage/' . $equipment->manual_url) }}" class="bg-blue-100 text-blue-600 px-4 py-2 rounded-lg text-center hover:bg-blue-200 transition">
                    View Manual Book
                </a>
                @endif
                
                @if($equipment->sop_url)
                <a href="{{ asset('storage/' . $equipment->sop_url) }}" class="bg-green-100 text-green-600 px-4 py-2 rounded-lg text-center hover:bg-green-200 transition">
                    View SOP Document
                </a>
                @endif
            </div>
        </div>
        
        <div class="px-6 py-4 bg-gray-50 text-center text-xs text-gray-500">
            <p>Scanned at: {{ date('d M Y H:i:s') }}</p>
            <p class="mt-1">Direct access by serial number: <a href="{{ url('/equipment/serial/' . urlencode($equipment->serial_number)) }}" class="text-blue-600 underline">{{ $equipment->serial_number }}</a></p>
            <p class="mt-1">
                <a href="{{ url('/q/' . urlencode($equipment->qr_code)) }}" class="text-blue-600 underline">QR Code Link</a>
            </p>
        </div>
    </div>
</body>
</html> 