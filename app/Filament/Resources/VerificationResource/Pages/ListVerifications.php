<?php

namespace App\Filament\Resources\VerificationResource\Pages;

use App\Filament\Resources\VerificationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Filament\Support\Enums\MaxWidth;
use App\Models\Inspection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class ListVerifications extends ListRecords
{
    protected static string $resource = VerificationResource::class;

    public $forceReload = false;

    protected function getHeaderActions(): array
    {
        return [];
    }
    
    protected function getTablePollingInterval(): ?string
    {
        // Auto-refresh tabel setiap 2 detik untuk memastikan data terbaru
        return '2s';
    }
    
    public function mount(): void
    {
        parent::mount();
        
        // Force bersihkan cache setiap kali halaman dibuka
        Cache::flush();
        DB::connection()->flushQueryLog();
        
        // Gunakan pendekatan yang lebih aman untuk isolation level
        try {
            // Coba set untuk MySQL versi yang lebih baru
            DB::statement("SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
        } catch (\Exception $e) {
            try {
                // Coba set untuk MySQL versi yang lebih lama
                DB::statement("SET SESSION tx_isolation='READ-UNCOMMITTED'");
            } catch (\Exception $e) {
                // Abaikan error jika tidak bisa mengubah isolation level
                Log::warning('Cannot set transaction isolation level', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    protected function getTableQuery(): ?Builder
    {
        // Override query default untuk memaksa pengambilan ulang data
        $query = parent::getTableQuery();
        
        if ($query === null) {
            return null;
        }
        
        // Tambahkan cache buster untuk mencegah caching
        $timestamp = time();
        $query->where(function($q) use ($timestamp) {
            $q->where('id', '>', 0);
            
            // Debug query tanpa mencoba mengakses variabel sistem MySQL yang tidak kompatibel
            Log::info('Table query executed', [
                'cache_buster' => $timestamp,
                'timestamp' => now()->toDateTimeString()
            ]);
        });

        // Pastikan benar-benar hanya menampilkan yang status completed
        $query->whereRaw("status = 'completed'");
        
        // Eager load relasi equipment dan technician untuk menghindari N+1 query
        $query->with(['equipment', 'technician']);
        
        return $query;
    }
        
    protected function getTableMaxHeight(): ?string
    {
        return '75vh';
    }

    protected function getTableEmptyStateHeading(): string
    {
        return 'Tidak ada inspeksi yang perlu diverifikasi';
    }
    
    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Tidak ada inspeksi dengan status "Completed" yang perlu diverifikasi saat ini.';
    }
    
    protected function getTableEmptyStateIcon(): string
    {
        return 'heroicon-o-check-circle';
    }
    
    protected function getTableDescription(): ?string
    {
        $inspectionCount = Inspection::where('status', 'completed')->count();
        return "Total inspeksi yang perlu diverifikasi: {$inspectionCount}";
    }
    
    protected function onMount(): void
    {
        // Tampilkan notifikasi kepada pengguna tentang auto-refresh
        Notification::make()
            ->title('Halaman verification akan diperbarui otomatis')
            ->body('Data pada halaman ini akan diperbarui secara otomatis setiap 2 detik.')
            ->info()
            ->send();
        
        Log::info('User membuka halaman verification', [
            'user_id' => auth()->id(),
            'username' => auth()->user()->name
        ]);
    }
} 