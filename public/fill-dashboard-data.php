<?php
/**
 * Script untuk mengisi data dashboard Filament
 * 
 * PENTING: Hapus file ini setelah digunakan!
 */

// Load framework
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Verifikasi akses dengan token sederhana untuk keamanan
$token = $_GET['token'] ?? '';
if ($token !== 'gapura123') {
    die('Akses ditolak! Gunakan token yang valid.');
}

use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\PredictiveMaintenance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Mulai output
echo '<h1>Dashboard Data Filler</h1>';
echo '<p>Mengisi data untuk dashboard Filament...</p>';

// Bagian 1: Perbarui data maintenance untuk kalender
echo '<h2>1. Mengisi data maintenance untuk kalender</h2>';

$equipments = Equipment::all();
$now = Carbon::now();

// Periksa jika maintenance kosong
if (Maintenance::count() < 5) {
    echo '<p>Data maintenance tidak cukup. Menambahkan data contoh...</p>';
    
    // Hapus semua data lama jika kurang dari 5
    if (Maintenance::count() > 0) {
        echo '<p>Menghapus ' . Maintenance::count() . ' data maintenance lama</p>';
        DB::table('maintenances')->delete();
    }
    
    // Tambah 10 maintenance untuk kalender
    foreach (range(1, 10) as $i) {
        $equipment = $equipments->random();
        $startDate = $now->copy()->addDays(rand(-5, 15))->format('Y-m-d H:i:s');
        
        $status = ['scheduled', 'in-progress', 'completed'][rand(0, 2)];
        
        $maintenanceData = [
            'equipment_id' => $equipment->id,
            'schedule_date' => $startDate,
            'next_service_date' => Carbon::parse($startDate)->addMonths(1)->format('Y-m-d H:i:s'),
            'technician_id' => 1, // Default user/teknisi
            'maintenance_type' => ['preventive', 'corrective'][rand(0, 1)],
            'status' => $status,
            'result' => $status == 'completed' ? ['good', 'partial', 'failed'][rand(0, 2)] : null,
            'cost' => rand(50, 500) * 1000,
            'notes' => 'Maintenance untuk ' . $equipment->name,
            'equipment_type' => $equipment->type,
            'priority' => $equipment->priority,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        
        if ($status == 'completed') {
            $maintenanceData['actual_date'] = Carbon::parse($startDate)->subDays(rand(0, 3))->format('Y-m-d H:i:s');
            $maintenanceData['approval_status'] = ['pending', 'approved', 'rejected'][rand(0, 2)];
        }
        
        DB::table('maintenances')->insert($maintenanceData);
        echo '<p>Menambahkan maintenance untuk ' . $equipment->name . '</p>';
    }
    
    echo '<p><strong>Berhasil menambahkan 10 data maintenance contoh.</strong></p>';
} else {
    echo '<p>Data maintenance tersedia: ' . Maintenance::count() . ' entri.</p>';
    
    // Pastikan semua maintenance memiliki equipment_type dan priority
    $updated = 0;
    foreach (Maintenance::whereNull('equipment_type')->orWhereNull('priority')->get() as $maintenance) {
        if ($maintenance->equipment) {
            $maintenance->equipment_type = $maintenance->equipment->type;
            $maintenance->priority = $maintenance->equipment->priority;
            $maintenance->save();
            $updated++;
        }
    }
    
    if ($updated > 0) {
        echo "<p>Memperbaiki $updated maintenance dengan data equipment.</p>";
    }
}

// Bagian 2: Isi tabel predictive_maintenances
echo '<h2>2. Mengisi data predictive maintenance</h2>';

// Periksa apakah tabel predictive_maintenances ada
if (!Schema::hasTable('predictive_maintenances')) {
    echo '<p style="color:red;"><strong>Error:</strong> Tabel predictive_maintenances tidak ditemukan! Jalankan migrasi terlebih dahulu.</p>';
    echo '<pre>php artisan migrate</pre>';
    
    // Coba buat tabel secara manual
    echo '<p>Mencoba membuat tabel secara manual...</p>';
    
    try {
        Schema::create('predictive_maintenances', function ($table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->onDelete('cascade');
            $table->timestamp('last_maintenance_date')->nullable();
            $table->timestamp('next_maintenance_date')->nullable();
            $table->float('condition_score')->nullable()->comment('Skor kondisi peralatan (0-100)');
            $table->string('recommendation')->nullable();
            $table->timestamps();
        });
        
        echo '<p style="color:green;">Berhasil membuat tabel predictive_maintenances!</p>';
    } catch (Exception $e) {
        echo '<p style="color:red;">Gagal membuat tabel: ' . $e->getMessage() . '</p>';
        die('Proses dihentikan.');
    }
}

// Periksa data yang ada
$count = DB::table('predictive_maintenances')->count();
if ($count > 0) {
    echo "<p>Data predictive maintenance tersedia: $count entri.</p>";
    
    // Hapus data lama jika diminta
    if (isset($_GET['refresh']) && $_GET['refresh'] === 'true') {
        DB::table('predictive_maintenances')->delete();
        echo '<p>Menghapus data lama untuk refresh...</p>';
        $count = 0;
    }
}

if ($count === 0) {
    echo '<p>Tidak ada data predictive maintenance. Menambahkan data...</p>';
    
    // Ambil semua equipment
    $equipments = DB::table('equipments')->get();
    $now = now();
    
    foreach ($equipments as $equipment) {
        // Cari maintenance terakhir untuk equipment ini
        $lastMaintenance = DB::table('maintenances')
            ->where('equipment_id', $equipment->id)
            ->where('status', 'completed')
            ->orderBy('actual_date', 'desc')
            ->first();
        
        $lastMaintenanceDate = $lastMaintenance ? $lastMaintenance->actual_date : null;
        
        // Generate prediksi tanggal maintenance berikutnya
        $nextMaintenanceDate = $lastMaintenanceDate 
            ? date('Y-m-d H:i:s', strtotime($lastMaintenanceDate . ' + ' . rand(30, 90) . ' days')) 
            : date('Y-m-d H:i:s', strtotime('+ ' . rand(10, 30) . ' days'));
        
        // Generate skor kondisi acak (untuk demo)
        $conditionScore = rand(50, 95);
        
        // Generate rekomendasi berdasarkan skor
        $recommendation = $conditionScore > 80 
            ? 'Routine maintenance recommended' 
            : ($conditionScore > 60 
                ? 'Inspection needed' 
                : 'Immediate maintenance required');
        
        // Insert data
        DB::table('predictive_maintenances')->insert([
            'equipment_id' => $equipment->id,
            'last_maintenance_date' => $lastMaintenanceDate,
            'next_maintenance_date' => $nextMaintenanceDate,
            'condition_score' => $conditionScore,
            'recommendation' => $recommendation,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        
        echo '<p>Menambahkan predictive maintenance untuk equipment ID ' . $equipment->id . '</p>';
    }
    
    echo '<p><strong>Berhasil menambahkan data predictive maintenance untuk ' . count($equipments) . ' equipment.</strong></p>';
}

// Bersihkan cache
echo '<h2>3. Membersihkan cache</h2>';

try {
    Artisan::call('cache:clear');
    echo '<p>Cache cleared</p>';
    
    Artisan::call('view:clear');
    echo '<p>View cache cleared</p>';
    
    Artisan::call('config:clear');
    echo '<p>Config cache cleared</p>';
    
    Artisan::call('route:clear');
    echo '<p>Route cache cleared</p>';
    
    echo '<p style="color:green;"><strong>Semua cache berhasil dibersihkan!</strong></p>';
} catch (Exception $e) {
    echo '<p style="color:red;">Error saat membersihkan cache: ' . $e->getMessage() . '</p>';
}

echo '<h2>Selesai!</h2>';
echo '<p>Semua data dashboard telah diisi. Silakan refresh dashboard Filament Anda.</p>';
echo '<p><a href="/admin" style="color:blue;">Kembali ke Dashboard</a></p>';

echo '<p style="color:red;"><strong>PENTING:</strong> Hapus file ini setelah digunakan untuk keamanan!</p>'; 