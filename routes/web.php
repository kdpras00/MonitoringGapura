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
});

// Route untuk menampilkan detail Equipment berdasarkan QR Code (tanpa autentikasi)
Route::get('/equipment/qr/{code}', action: [EquipmentController::class, 'showByQr'])->name('equipment.qr');

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
