<?php
/**
 * Script perbaikan kalender maintenance dan data predictive maintenance
 * PENTING: Hapus file ini setelah digunakan untuk keamanan
 */

// Load framework
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Keamanan sederhana
if (!isset($_GET['token']) || $_GET['token'] !== 'gapura123') {
    die("Akses ditolak! Silakan gunakan token yang valid.");
}

use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\PredictiveMaintenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

echo '<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
.section { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 5px solid #4CAF50; }
.error { border-left: 5px solid #F44336; }
.warning { border-left: 5px solid #FF9800; }
h1, h2 { color: #2c3e50; }
.btn { display: inline-block; padding: 10px 15px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-top: 10px; }
.btn-danger { background: #F44336; }
pre { background: #f1f1f1; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>';

echo '<h1>Dashboard Refresh Tool</h1>';
echo '<div class="section">';
echo '<p>Tool ini akan:</p>';
echo '<ul>';
echo '<li>Memperbaiki kalender maintenance</li>';
echo '<li>Mengisi data predictive maintenance</li>';
echo '<li>Membersihkan semua cache</li>';
echo '</ul>';
echo '</div>';

// STEP 1: Periksa tabel predictive_maintenances
echo '<h2>1. Memeriksa tabel predictive_maintenances</h2>';
echo '<div class="section">';

if (!Schema::hasTable('predictive_maintenances')) {
    echo '<div class="section error">';
    echo '<p><strong>Error:</strong> Tabel predictive_maintenances belum tersedia!</p>';
    echo '<p>Mencoba membuat tabel...</p>';
    
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
        echo '<p>✅ Tabel berhasil dibuat!</p>';
    } catch (\Exception $e) {
        echo '<p>❌ Gagal membuat tabel: ' . $e->getMessage() . '</p>';
        echo '<p>Silakan jalankan migrasi secara manual:</p>';
        echo '<pre>php artisan migrate</pre>';
        die('</div>');
    }
    echo '</div>';
} else {
    echo '<p>✅ Tabel predictive_maintenances sudah tersedia.</p>';
}
echo '</div>';

// STEP 2: Mengisi data maintenance
echo '<h2>2. Mengisi data maintenance</h2>';
echo '<div class="section">';

$maintenanceCount = Maintenance::count();
echo "<p>Jumlah data maintenance: $maintenanceCount entri.</p>";

if ($maintenanceCount < 5 || isset($_GET['force_maintenance'])) {
    echo '<p>Menambahkan data maintenance baru...</p>';
    
    // Hapus data lama jika diminta
    if (isset($_GET['force_maintenance']) && $maintenanceCount > 0) {
        DB::table('maintenances')->delete();
        echo "<p>Data maintenance lama dihapus.</p>";
        $maintenanceCount = 0;
    }
    
    $equipments = Equipment::all();
    $now = Carbon::now();
    $count = 0;
    
    if ($equipments->count() == 0) {
        echo '<p class="warning">⚠️ Tidak ada equipment untuk dibuat maintenance!</p>';
    } else {
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
            $count++;
        }
        echo "<p>✅ Berhasil menambahkan $count maintenance baru.</p>";
    }
} else {
    echo '<p>✅ Data maintenance sudah cukup tersedia.</p>';
    echo '<p>Untuk menambahkan maintenance baru, tambahkan parameter <code>force_maintenance=1</code> pada URL.</p>';
}

echo '</div>';

// STEP 3: Mengisi data predictive maintenance
echo '<h2>3. Mengisi data predictive maintenance</h2>';
echo '<div class="section">';

$pmCount = DB::table('predictive_maintenances')->count();
echo "<p>Jumlah data predictive maintenance: $pmCount entri.</p>";

if ($pmCount < 1 || isset($_GET['force_predictive'])) {
    echo '<p>Menambahkan data predictive maintenance baru...</p>';
    
    // Hapus data lama jika diminta
    if (isset($_GET['force_predictive']) && $pmCount > 0) {
        DB::table('predictive_maintenances')->delete();
        echo "<p>Data predictive maintenance lama dihapus.</p>";
    }
    
    $equipments = DB::table('equipments')->get();
    $now = Carbon::now();
    $count = 0;
    
    if ($equipments->count() == 0) {
        echo '<p class="warning">⚠️ Tidak ada equipment untuk dibuat predictive maintenance!</p>';
    } else {
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
            
            // Generate skor kondisi
            $conditionScore = rand(50, 95);
            
            // Generate rekomendasi
            $recommendation = $conditionScore > 80 
                ? 'Kondisi peralatan baik, maintenance rutin direkomendasikan' 
                : ($conditionScore > 60 
                    ? 'Periksa peralatan secara berkala, maintenance segera dijadwalkan' 
                    : 'Maintenance segera diperlukan, kondisi alat memerlukan perhatian');
            
            // Tambahkan data
            DB::table('predictive_maintenances')->insert([
                'equipment_id' => $equipment->id,
                'last_maintenance_date' => $lastMaintenanceDate,
                'next_maintenance_date' => $nextMaintenanceDate,
                'condition_score' => $conditionScore,
                'recommendation' => $recommendation,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            
            $count++;
        }
        
        echo "<p>✅ Berhasil menambahkan $count predictive maintenance baru.</p>";
    }
} else {
    echo '<p>✅ Data predictive maintenance sudah tersedia.</p>';
    echo '<p>Untuk mengisi ulang predictive maintenance, tambahkan parameter <code>force_predictive=1</code> pada URL.</p>';
}

echo '</div>';

// STEP 4: Bersihkan cache
echo '<h2>4. Membersihkan cache</h2>';
echo '<div class="section">';

try {
    Artisan::call('cache:clear');
    echo '<p>✅ Cache bersih</p>';
    
    Artisan::call('view:clear');
    echo '<p>✅ View cache bersih</p>';
    
    Artisan::call('config:clear');
    echo '<p>✅ Config cache bersih</p>';
    
    Artisan::call('route:clear');
    echo '<p>✅ Route cache bersih</p>';
} catch (\Exception $e) {
    echo '<p class="error">❌ Error saat membersihkan cache: ' . $e->getMessage() . '</p>';
}

echo '</div>';

// STEP 5: Selesai
echo '<h2>Selesai!</h2>';
echo '<div class="section">';
echo '<p>Semua perubahan telah berhasil diterapkan. Silakan refresh dashboard Filament.</p>';
echo '<p><a href="/admin" class="btn">Kembali ke Dashboard</a></p>';
echo '</div>';

echo '<div class="section warning">';
echo '<p><strong>PENTING:</strong> Hapus file ini setelah digunakan untuk alasan keamanan!</p>';
echo '<p><a href="#" class="btn btn-danger" onclick="alert(\'Silakan hapus file ini secara manual dari server\'); return false;">Hapus File Ini</a></p>';
echo '</div>'; 