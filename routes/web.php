<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PredictiveMaintenanceController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\MaintenanceController;
use Filament\Http\Middleware\Authenticate;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


Route::post('/admin/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/admin'); // Redirect ke dashboard admin
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ]);
})->name('filament.auth.attempt');

Route::get('/', function () {
    // return view('home');
    return redirect('/admin');
});

// Public routes for QR Code access (no authentication required)
Route::middleware(['web'])->group(function () {
    // Test route to check if routing is working
    Route::get('/equipment/test', function () {
        $serials = App\Models\Equipment::pluck('serial_number', 'id')->toArray();
        return response()->json([
            'message' => 'Routes are working correctly',
            'equipment_serials' => $serials,
            'test_links' => [
                'serial_example' => url('/equipment/serial/' . urlencode(array_values($serials)[0] ?? 'test')),
                'qr_example' => url('/q/test'),
            ]
        ]);
    })->name('equipment.test');

    // QR code test page
    Route::get('/qr-test', function () {
        return view('test-qr');
    })->name('qr.test');

    // URL test page
    Route::get('/url-test', function () {
        return view('test-url');
    })->name('url.test');

    // Direct QR access page
    Route::get('/qr-direct', function () {
        return view('qr-direct');
    })->name('qr.direct');

    // Short URL for QR code access
    Route::get('/q/{code}', [EquipmentController::class, 'quickAccess'])->name('equipment.quick');

    // Original route for QR code
    Route::get('/equipment/qr/{code}', [EquipmentController::class, 'showByQr'])->name('equipment.qr');

    // Access equipment by serial number
    Route::get('/equipment/serial/{serial}', [EquipmentController::class, 'showBySerial'])->name('equipment.serial');
});

// Kelompok route yang membutuhkan autentikasi
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profil User
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Reporting (Admin)
    Route::prefix('admin/reports')->name('reports.')->group(function () {
        Route::get('/equipment', [ReportController::class, 'equipment'])->name('equipment');
        Route::get('/maintenance', [ReportController::class, 'maintenance'])->name('maintenance');
    });

    // Notifications (Admin)
    Route::prefix('admin/notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-as-read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
    });

    // Predictive Maintenance (Admin)
    Route::get('/admin/predictive-maintenance', [PredictiveMaintenanceController::class, 'index'])
        ->name('predictive.maintenance');

    // Inventory Management (Admin)
    Route::resource('/admin/inventory', InventoryController::class);

    // Print QR Code
    Route::get('/equipment/{id}/print-qr', [EquipmentController::class, 'printQrCode'])->name('equipment.print-qr');
});

// Memasukkan route authentication dari Laravel Breeze/Fortify
require __DIR__ . '/auth.php';

Route::get('/equipment/{equipment}', [EquipmentController::class, 'show'])->name('equipment.show');

Route::get('/maintenance/{maintenance}', [MaintenanceController::class, 'show'])->name('maintenance.show')->middleware('can:view,maintenance');

Route::get('/maintenance/{maintenance}/edit', [MaintenanceController::class, 'edit'])
    ->name('maintenance.edit');


Route::resource('maintenance', MaintenanceController::class);


Route::get('/report/maintenance', [ReportController::class, 'maintenanceReport'])
    ->name('report.maintenance');


// routes/web.php
Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])
    ->name('notifications.mark-all-as-read');


Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])
    ->middleware('auth')
    ->name('notifications.mark-all-as-read');




Filament::registerPages([
    Dashboard::class,
]);

// Maintenance Routes
Route::prefix('maintenance')->name('maintenance.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\MaintenanceController::class, 'dashboard'])->name('dashboard');
    Route::get('/detail/{id}', [App\Http\Controllers\MaintenanceController::class, 'detail'])->name('detail');
    Route::post('/schedule/{id}', [App\Http\Controllers\MaintenanceController::class, 'schedule'])->name('schedule');
    Route::post('/complete/{id}', [App\Http\Controllers\MaintenanceController::class, 'complete'])->name('complete');
    
    // Supervisor Routes
    Route::get('/supervisor', [App\Http\Controllers\MaintenanceController::class, 'supervisor'])
        ->name('supervisor')
        ->middleware(['supervisor']);
    Route::get('/approval/{id}', [App\Http\Controllers\MaintenanceController::class, 'approvalDetail'])
        ->name('approval.detail')
        ->middleware(['supervisor']);
    Route::post('/approve/{id}', [App\Http\Controllers\MaintenanceController::class, 'approve'])
        ->name('approve')
        ->middleware(['supervisor']);
    
    // Export Routes
    Route::get('/export', [App\Http\Controllers\MaintenanceController::class, 'export'])->name('export-reports');
    Route::post('/generate-export', [App\Http\Controllers\MaintenanceController::class, 'generateExport'])->name('generate-export');
    
    // History Detail Route
    Route::get('/history/{id}', [App\Http\Controllers\MaintenanceController::class, 'historyDetail'])->name('history.detail');
    
    // Refresh Data Route
    Route::get('/refresh', [App\Http\Controllers\MaintenanceController::class, 'refreshData'])->name('refresh');
});

// Testing Route
// Route::get('/test-equipments', function () {
//     $equipment = \App\Models\Equipment::all();
//     return response()->json($equipment);
// });

// Route::get('/test-maintenances', function () {
//     $maintenances = \App\Models\Maintenance::all();
//     return response()->json($maintenances);
// });

// Spare Part Barcode Routes
Route::prefix('spare-parts')->name('spare-parts.')->group(function () {
    // Route cetak barcode (memerlukan autentikasi)
    Route::get('/barcode/{sparePart}', [App\Http\Controllers\SparePartController::class, 'barcode'])
        ->name('barcode')
        ->middleware(['auth']);
    
    // Route scan barcode (tidak memerlukan autentikasi)
    Route::get('/scan', [App\Http\Controllers\SparePartController::class, 'scan'])
        ->name('scan');
    
    // Route untuk barcode tidak ditemukan (tidak memerlukan autentikasi)
    Route::get('/not-found', [App\Http\Controllers\SparePartController::class, 'notFound'])
        ->name('not-found');
});

// Equipment Barcode Routes
Route::prefix('equipment')->name('equipment.')->group(function () {
    // Route cetak barcode (memerlukan autentikasi)
    Route::get('/barcode/{equipment}', [App\Http\Controllers\EquipmentController::class, 'barcode'])
        ->name('barcode')
        ->middleware(['auth']);
});

// Public Equipment Routes (tidak memerlukan autentikasi)
Route::get('/equipment/scan', [App\Http\Controllers\EquipmentController::class, 'scan'])
    ->name('equipment.scan');

Route::get('/equipment/not-found', [App\Http\Controllers\EquipmentController::class, 'notFound'])
    ->name('equipment.not-found');
    
// Route view equipment langsung dengan ID
Route::get('/equipment/view/{id}', [App\Http\Controllers\EquipmentController::class, 'viewById'])
    ->name('equipment.view-by-id');
    
// Test route untuk debugging
Route::get('/equipment/test-scan', function() {
    $equipment = \App\Models\Equipment::first();
    if ($equipment) {
        return [
            'status' => 'success',
            'equipment' => $equipment->only(['id', 'name', 'serial_number', 'barcode', 'qr_code']),
            'scan_url' => url('/equipment/scan?code=' . $equipment->barcode),
            'not_found_url' => url('/equipment/not-found?code=NOTFOUND')
        ];
    }
    return ['status' => 'error', 'message' => 'No equipment found'];
});

// Route untuk menampilkan semua equipment (debugging)
Route::get('/equipment/list-all', function() {
    $equipments = \App\Models\Equipment::all();
    
    return [
        'count' => $equipments->count(),
        'equipments' => $equipments->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'serial_number' => $item->serial_number,
                'barcode' => $item->barcode,
                'qr_code' => $item->qr_code,
                'view_url' => url('/equipment/view/' . $item->id)
            ];
        })
    ];
});
