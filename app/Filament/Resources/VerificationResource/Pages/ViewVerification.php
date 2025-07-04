<?php

namespace App\Filament\Resources\VerificationResource\Pages;

use App\Filament\Resources\VerificationResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use App\Models\Inspection;
use App\Models\Maintenance;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;

class ViewVerification extends ViewRecord
{
    protected static string $resource = VerificationResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Inspection')
                    ->schema([
                        TextEntry::make('equipment.name')
                            ->label('Peralatan')
                            ->weight('bold'),
                        TextEntry::make('inspection_date')
                            ->label('Tanggal Inspeksi')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('technician.name')
                            ->label('Teknisi'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'completed' => 'info',
                                'verified' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('notes')
                            ->label('Catatan Teknisi')
                            ->markdown(),
                        TextEntry::make('verification_notes')
                            ->label('Catatan Verifikasi')
                            ->markdown()
                            ->visible(fn ($record) => !empty($record->verification_notes)),
                    ])
                    ->columns(1),
                
                Section::make('Foto Inspeksi')
                    ->schema([
                        ImageEntry::make('before_image')
                            ->label('Foto Sebelum')
                            ->visibility('public')
                            ->columnSpanFull(),
                        ImageEntry::make('after_image')
                            ->label('Foto Setelah')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('verify')
                ->label('Verifikasi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Verifikasi Inspeksi')
                ->modalDescription('Apakah Anda yakin ingin memverifikasi inspeksi ini?')
                ->modalSubmitActionLabel('Ya, Verifikasi')
                ->form([
                    \Filament\Forms\Components\Textarea::make('verification_notes')
                        ->label('Catatan Verifikasi'),
                ])
                ->action(function (array $data) {
                    try {
                        $record = $this->getRecord();
                        
                        // Log tindakan
                        Log::info('Mencoba memverifikasi inspeksi dari halaman view', [
                            'inspection_id' => $record->id,
                            'user_id' => auth()->id()
                        ]);
                        
                        // Gunakan direct query update untuk memastikan data berubah
                        $userId = auth()->id();
                        DB::transaction(function() use ($record, $data, $userId) {
                            // Gunakan prepared statement untuk memastikan semua nilai diescaped dengan benar
                            $sql = "UPDATE `inspections` SET 
                                  `status` = ?,
                                  `verification_notes` = ?,
                                  `verification_date` = NOW(),
                                  `verified_by` = ?,
                                  `updated_at` = NOW()
                                WHERE `id` = ?";

                            DB::statement($sql, ['verified', $data['verification_notes'] ?? null, $userId, $record->id]);
                            
                            // Update status maintenance menjadi completed (terverifikasi) jika ada
                            $maintenance = null;
                            if ($record->maintenance_id) {
                                $maintenance = \App\Models\Maintenance::find($record->maintenance_id);
                            }
                            
                            if (!$maintenance) {
                                $maintenance = \App\Models\Maintenance::where('equipment_id', $record->equipment_id)
                                    ->where('technician_id', $record->technician_id)
                                    ->whereIn('status', ['pending', 'planned', 'assigned', 'in-progress', 'pending-verification'])
                                    ->latest()
                                    ->first();
                            }

                            if ($maintenance) {
                                // Update status dan data maintenance
                                $maintenance->status = Maintenance::STATUS_VERIFIED;  // verified berarti sudah terverifikasi
                                $maintenance->approval_status = 'approved';
                                $maintenance->approval_notes = $data['verification_notes'] ?? 'Terverifikasi oleh supervisor';
                                $maintenance->approved_by = $userId;
                                $maintenance->approval_date = now();
                                $maintenance->actual_date = now();
                                $maintenance->save();
                                
                                // Log perubahan maintenance
                                Log::info('Maintenance terverifikasi', [
                                    'maintenance_id' => $maintenance->id,
                                    'status' => $maintenance->status
                                ]);
                            }
                        });
                        
                        // Debug verifikasi
                        Log::info('Verifikasi selesai dari halaman view', [
                            'inspection_id' => $record->id,
                            'new_status' => DB::table('inspections')->where('id', $record->id)->value('status')
                        ]);

                        // Force flush cache
                        DB::connection()->flushQueryLog();
                        Cache::flush();

                        Notification::make()
                            ->title('Inspection berhasil diverifikasi')
                            ->success()
                            ->send();

                        // Force hard redirect ke halaman daftar dengan refresh
                        return response()->redirectTo(VerificationResource::getUrl())->header('Cache-Control', 'no-cache, no-store, must-revalidate');
                    } catch (\Exception $e) {
                        Log::error('Error verifikasi', [
                            'inspection_id' => $this->getRecord()->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        Notification::make()
                            ->title('Gagal memverifikasi inspeksi')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->getRecord()->status === 'pending-verification'),

            Action::make('reject')
                ->label('Tolak')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Tolak Inspeksi')
                ->modalDescription('Apakah Anda yakin ingin menolak inspeksi ini?')
                ->modalSubmitActionLabel('Ya, Tolak')
                ->form([
                    \Filament\Forms\Components\Textarea::make('verification_notes')
                        ->label('Alasan Penolakan')
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $record = $this->getRecord();
                        
                        // Log tindakan
                        Log::info('Mencoba menolak inspeksi dari halaman view', [
                            'inspection_id' => $record->id,
                            'user_id' => auth()->id()
                        ]);
                        
                        // Gunakan direct query update untuk memastikan data berubah
                        $userId = auth()->id();
                        DB::transaction(function() use ($record, $data, $userId) {
                            // Gunakan prepared statement untuk memastikan semua nilai diescaped dengan benar
                            $sql = "UPDATE `inspections` SET 
                                  `status` = ?,
                                  `verification_notes` = ?,
                                  `verification_date` = NOW(),
                                  `verified_by` = ?,
                                  `updated_at` = NOW()
                                WHERE `id` = ?";

                            DB::statement($sql, ['rejected', $data['verification_notes'], $userId, $record->id]);
                            
                            // Update status maintenance menjadi rejected jika ada
                            $maintenance = null;
                            if ($record->maintenance_id) {
                                $maintenance = \App\Models\Maintenance::find($record->maintenance_id);
                            }
                            
                            if (!$maintenance) {
                                $maintenance = \App\Models\Maintenance::where('equipment_id', $record->equipment_id)
                                    ->where('technician_id', $record->technician_id)
                                    ->whereIn('status', ['pending', 'planned', 'assigned', 'in-progress', 'pending-verification'])
                                    ->latest()
                                    ->first();
                            }

                            if ($maintenance) {
                                // Update status dan data maintenance
                                $maintenance->status = Maintenance::STATUS_REJECTED;
                                $maintenance->approval_status = 'rejected';
                                $maintenance->approval_notes = $data['verification_notes'];
                                $maintenance->approved_by = $userId;
                                $maintenance->approval_date = now();
                                $maintenance->save();
                                
                                // Log perubahan maintenance
                                Log::info('Maintenance ditolak', [
                                    'maintenance_id' => $maintenance->id,
                                    'status' => $maintenance->status
                                ]);
                            }
                        });
                        
                        // Debug penolakan
                        Log::info('Penolakan selesai dari halaman view', [
                            'inspection_id' => $record->id,
                            'new_status' => DB::table('inspections')->where('id', $record->id)->value('status')
                        ]);

                        // Force flush cache
                        DB::connection()->flushQueryLog();
                        Cache::flush();

                        Notification::make()
                            ->title('Inspection ditolak')
                            ->danger()
                            ->send();

                        // Force hard redirect ke halaman daftar
                        return response()->redirectTo(VerificationResource::getUrl())->header('Cache-Control', 'no-cache, no-store, must-revalidate');
                    } catch (\Exception $e) {
                        Log::error('Error penolakan', [
                            'inspection_id' => $this->getRecord()->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        Notification::make()
                            ->title('Gagal menolak inspeksi')
                            ->body('Terjadi kesalahan: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->getRecord()->status === 'pending-verification'),
        ];
    }

    public function getRecord(): \Illuminate\Database\Eloquent\Model
    {
        $record = parent::getRecord();
        
        // Pastikan relasi equipment dan technician selalu di-load
        if ($record && !$record->relationLoaded('equipment')) {
            $record->load(['equipment', 'technician']);
        }
        
        return $record;
    }
}
