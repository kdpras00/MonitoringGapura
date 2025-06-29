@php
use Illuminate\Support\Facades\Storage;
@endphp

<x-filament::page>
    <x-filament::section>
        <h2 class="text-xl font-bold mb-4">Upload Foto Inspeksi</h2>
        
        @if(!$this->record->isVerified() && !$this->record->isRejected())
            <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="text-blue-700 font-medium mb-2">Panduan Upload Foto</h3>
                <ul class="list-disc list-inside text-blue-600 space-y-1">
                    <li>Foto sebelum inspeksi wajib diupload saat memulai inspeksi</li>
                    <li>Foto sesudah inspeksi wajib diupload saat menyelesaikan inspeksi</li>
                    <li>Pastikan foto yang diupload jelas dan menunjukkan kondisi peralatan dengan baik</li>
                    <li>Jika terjadi kesalahan upload, gunakan tombol "Hapus Foto" di bagian atas halaman</li>
                    <li>Jika salah memilih status "Selesai", gunakan tombol "Reset Status" di bagian atas halaman</li>
                    <li>Izinkan akses lokasi untuk mencatat posisi saat inspeksi</li>
                </ul>
            </div>
            
            @if($this->record->status === 'completed')
                <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-yellow-800 font-medium">Perhatian: Status Inspeksi "Selesai"</h3>
                            <p class="text-yellow-700 mt-1">
                                Anda telah mengatur status inspeksi menjadi "Selesai". Pastikan foto sesudah inspeksi sudah diupload dan data yang dimasukkan sudah benar.
                                Setelah diverifikasi oleh supervisor, data tidak dapat diubah lagi.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-gray-800 font-medium">Inspeksi Sudah Diproses</h3>
                        <p class="text-gray-700 mt-1">
                            Inspeksi ini sudah {{ $this->record->isVerified() ? 'diverifikasi' : 'ditolak' }} oleh supervisor dan tidak dapat diubah lagi.
                            Anda hanya dapat melihat data inspeksi.
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="mb-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h3 class="text-lg font-medium mb-2">Detail Inspeksi</h3>
                    <div class="space-y-2">
                        <div>
                            <span class="font-medium">Peralatan:</span> 
                            {{ $this->record->equipment?->name ?? 'Tidak ada data' }}
                        </div>
                        <div>
                            <span class="font-medium">Tanggal Inspeksi:</span> 
                            {{ $this->record->inspection_date?->format('d M Y H:i') ?? 'Tidak ada data' }}
                        </div>
                        <div>
                            <span class="font-medium">Status:</span> 
                            <span class="
                                @if($this->record->status === 'pending') text-yellow-600
                                @elseif($this->record->status === 'completed') text-green-600
                                @elseif($this->record->status === 'verified') text-blue-600
                                @else text-red-600
                                @endif font-medium
                            ">
                                @if($this->record->status === 'pending') Belum Selesai
                                @elseif($this->record->status === 'completed') Selesai
                                @elseif($this->record->status === 'verified') Terverifikasi
                                @else Ditolak
                                @endif
                            </span>
                        </div>
                        @if($this->record->status === 'completed' || $this->record->status === 'verified' || $this->record->status === 'rejected')
                        <div>
                            <span class="font-medium">Tanggal Penyelesaian:</span> 
                            {{ $this->record->completion_date?->format('d M Y H:i') ?? 'Belum diselesaikan' }}
                        </div>
                        @endif
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium mb-2">Instruksi</h3>
                    <p class="text-gray-600">
                        Upload foto sebelum dan sesudah inspeksi. Foto sebelum wajib diupload saat memulai inspeksi.
                        Foto sesudah wajib diupload saat menyelesaikan inspeksi.
                    </p>
                </div>
            </div>
            
            @if($this->record->before_image || $this->record->after_image)
            <div class="mt-4">
                <h3 class="text-lg font-medium mb-2">Preview Foto</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="font-medium mb-1">Foto Sebelum</p>
                        @if($this->record->before_image)
                            <img src="{{ Storage::disk('public')->url($this->record->before_image) }}" 
                                alt="Foto Sebelum" 
                                class="rounded-lg border border-gray-200 shadow-sm max-h-48 object-cover">
                        @else
                            <div class="bg-gray-100 rounded-lg border border-gray-200 p-4 text-center text-gray-500">
                                Belum ada foto
                            </div>
                        @endif
                    </div>
                    <div>
                        <p class="font-medium mb-1">Foto Sesudah</p>
                        @if($this->record->after_image)
                            <img src="{{ Storage::disk('public')->url($this->record->after_image) }}" 
                                alt="Foto Sesudah" 
                                class="rounded-lg border border-gray-200 shadow-sm max-h-48 object-cover">
                        @else
                            <div class="bg-gray-100 rounded-lg border border-gray-200 p-4 text-center text-gray-500">
                                Belum ada foto
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        @if(!$this->record->isVerified() && !$this->record->isRejected())
            <form wire:submit="save">
                {{ $this->form }}
                
                <div id="location-status" class="mt-4 p-3 bg-gray-100 rounded-lg hidden">
                    <p class="text-gray-700">
                        <span class="font-medium">Status Lokasi:</span>
                        <span id="location-status-text">Mendeteksi lokasi...</span>
                    </p>
                </div>
                
                <div class="mt-6 flex justify-center">
                    <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Simpan Foto Inspeksi
                    </button>
                </div>
            </form>
        @endif
    </x-filament::section>
</x-filament::page> 

@script
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Ambil elemen input lokasi
        const locationInput = document.querySelector('[name="location"]');
        const locationLatInput = document.querySelector('[name="location_lat"]');
        const locationLngInput = document.querySelector('[name="location_lng"]');
        const locationTimestampInput = document.querySelector('[name="location_timestamp"]');
        const locationStatus = document.getElementById('location-status');
        const locationStatusText = document.getElementById('location-status-text');

        // Tampilkan status lokasi
        locationStatus.classList.remove('hidden');
        
        // Cek apakah geolocation tersedia di browser
        if (navigator.geolocation) {
            locationStatusText.textContent = 'Mendeteksi lokasi saat ini...';
            
            // Minta akses ke geolocation
            navigator.geolocation.getCurrentPosition(
                // Success callback
                function(position) {
                    // Dapatkan koordinat
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    const accuracy = position.coords.accuracy;
                    
                    // Isi nilai input koordinat
                    if (locationLatInput) locationLatInput.value = latitude;
                    if (locationLngInput) locationLngInput.value = longitude;
                    if (locationTimestampInput) locationTimestampInput.value = new Date().toISOString();
                    
                    // Coba dapatkan nama lokasi berdasarkan koordinat (reverse geocoding)
                    fetch(`https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json`)
                        .then(response => response.json())
                        .then(data => {
                            let locationName = '';
                            
                            if (data && data.display_name) {
                                // Ambil nama lokasi dari respons
                                locationName = data.display_name;
                                
                                // Jika terlalu panjang, ambil hanya informasi penting
                                if (locationName.length > 100) {
                                    if (data.address) {
                                        const addressParts = [];
                                        if (data.address.road) addressParts.push(data.address.road);
                                        if (data.address.suburb) addressParts.push(data.address.suburb);
                                        if (data.address.city || data.address.town) addressParts.push(data.address.city || data.address.town);
                                        
                                        locationName = addressParts.join(', ');
                                    }
                                }
                            }
                            
                            // Jika nama lokasi tersedia, tambahkan ke input lokasi
                            if (locationInput && locationName && !locationInput.value) {
                                locationInput.value = locationName;
                            }
                            
                            // Update status
                            locationStatusText.textContent = `Lokasi terdeteksi dengan akurasi ${Math.round(accuracy)} meter`;
                            locationStatus.classList.remove('bg-gray-100');
                            locationStatus.classList.add('bg-green-50', 'border', 'border-green-200');
                        })
                        .catch(error => {
                            console.error('Error saat mendapatkan nama lokasi:', error);
                            
                            // Masih bisa menggunakan koordinat
                            locationStatusText.textContent = `Koordinat terdeteksi: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                            
                            // Isi input lokasi dengan koordinat jika kosong
                            if (locationInput && !locationInput.value) {
                                locationInput.value = `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                            }
                        });
                },
                // Error callback
                function(error) {
                    let errorMessage = 'Tidak dapat mendeteksi lokasi. ';
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Izin lokasi ditolak. Silakan aktifkan akses lokasi di pengaturan browser Anda.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Waktu permintaan lokasi habis.';
                            break;
                        case error.UNKNOWN_ERROR:
                            errorMessage += 'Terjadi kesalahan yang tidak diketahui.';
                            break;
                    }
                    
                    locationStatusText.textContent = errorMessage;
                    locationStatus.classList.remove('bg-gray-100');
                    locationStatus.classList.add('bg-yellow-50', 'border', 'border-yellow-200');
                    console.error('Geolocation error:', error);
                },
                // Options
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            // Browser tidak mendukung geolocation
            locationStatusText.textContent = 'Browser Anda tidak mendukung geolocation.';
            locationStatus.classList.remove('bg-gray-100');
            locationStatus.classList.add('bg-yellow-50', 'border', 'border-yellow-200');
        }
    });
</script>
@endscript 