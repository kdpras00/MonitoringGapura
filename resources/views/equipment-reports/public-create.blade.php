<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Laporan Kerusakan - MonitoringGapura</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-lg mx-auto bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Form Laporan Kerusakan Peralatan</h1>
                <p class="text-gray-600">Silakan isi form berikut untuk melaporkan kerusakan peralatan</p>
            </div>
            
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('public.report.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div>
                    <label for="reporter_name" class="block text-gray-700 font-medium mb-2">Nama Pelapor <span class="text-red-500">*</span></label>
                    <input type="text" id="reporter_name" name="reporter_name" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('reporter_name') }}" required>
                </div>
                
                <div>
                    <label for="equipment_type" class="block text-gray-700 font-medium mb-2">Tipe Peralatan <span class="text-red-500">*</span></label>
                    <select id="equipment_type" name="equipment_type" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">-- Pilih Tipe Peralatan --</option>
                        <option value="elektrik" {{ old('equipment_type') == 'elektrik' ? 'selected' : '' }}>Elektrik</option>
                        <option value="non-elektrik" {{ old('equipment_type') == 'non-elektrik' ? 'selected' : '' }}>Non-Elektrik</option>
                    </select>
                </div>
                
                <div>
                    <label for="equipment_id" class="block text-gray-700 font-medium mb-2">Pilih Peralatan <span class="text-red-500">*</span></label>
                    <select id="equipment_id" name="equipment_id" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">-- Pilih Peralatan --</option>
                        @foreach ($equipments as $equipment)
                            <option value="{{ $equipment->id }}" data-type="{{ $equipment->type }}" {{ old('equipment_id') == $equipment->id ? 'selected' : '' }}>
                                {{ $equipment->name }} ({{ $equipment->code ?? $equipment->serial_number }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="issue_description" class="block text-gray-700 font-medium mb-2">Deskripsi Kerusakan <span class="text-red-500">*</span></label>
                    <textarea id="issue_description" name="issue_description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>{{ old('issue_description') }}</textarea>
                </div>
                
                <div>
                    <label for="priority" class="block text-gray-700 font-medium mb-2">Tingkat Urgensi <span class="text-red-500">*</span></label>
                    <select id="priority" name="priority" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="hijau" {{ old('priority') == 'hijau' ? 'selected' : '' }}>Rendah - Masih bisa beroperasi</option>
                        <option value="kuning" {{ old('priority') == 'kuning' ? 'selected' : '' }}>Sedang - Beroperasi tapi terbatas</option>
                        <option value="merah" {{ old('priority') == 'merah' ? 'selected' : '' }}>Tinggi - Tidak bisa beroperasi</option>
                    </select>
                </div>
                
                <div>
                    <label for="location" class="block text-gray-700 font-medium mb-2">Lokasi</label>
                    <input type="text" id="location" name="location" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ old('location') }}">
                </div>
                
                <div>
                    <label for="issue_image" class="block text-gray-700 font-medium mb-2">Foto Kerusakan</label>
                    <input type="file" id="issue_image" name="issue_image" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Format: JPG, PNG. Ukuran maksimal: 2MB</p>
                </div>
                
                <div class="flex items-center justify-between pt-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Kirim Laporan
                    </button>
                    <a href="/" class="text-gray-600 hover:text-gray-800">Kembali</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Filter peralatan berdasarkan tipe yang dipilih
        document.addEventListener('DOMContentLoaded', function() {
            const equipmentTypeSelect = document.getElementById('equipment_type');
            const equipmentIdSelect = document.getElementById('equipment_id');
            const equipmentOptions = Array.from(equipmentIdSelect.options);
            
            equipmentTypeSelect.addEventListener('change', function() {
                const selectedType = this.value;
                
                // Reset equipment dropdown
                equipmentIdSelect.innerHTML = '<option value="">-- Pilih Peralatan --</option>';
                
                // Filter dan tambahkan equipment sesuai tipe
                if (selectedType) {
                    equipmentOptions.forEach(option => {
                        if (option.value && (option.dataset.type === selectedType || !option.dataset.type)) {
                            equipmentIdSelect.appendChild(option.cloneNode(true));
                        }
                    });
                } else {
                    // Jika tidak ada tipe yang dipilih, tampilkan semua
                    equipmentOptions.forEach(option => {
                        equipmentIdSelect.appendChild(option.cloneNode(true));
                    });
                }
            });
        });
    </script>
</body>
</html> 