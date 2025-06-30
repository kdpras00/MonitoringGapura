<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VerificationResource\Pages;
use App\Models\Inspection;
use App\Models\Maintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;

class VerificationResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Verification Inspection';
    protected static ?string $navigationGroup = 'Supervisor';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'verifications';
    protected static ?string $breadcrumb = 'Verification Inspection';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'supervisor';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Inspection')
                    ->schema([
                        Forms\Components\TextInput::make('equipment.name')
                            ->label('Peralatan')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('inspection_date')
                            ->label('Tanggal Inspeksi')
                            ->disabled(),
                        Forms\Components\TextInput::make('technician.name')
                            ->label('Teknisi')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Belum Selesai',
                                'in-progress' => 'Sedang Dikerjakan',
                                'completed' => 'Selesai',
                                'verified' => 'Terverifikasi',
                                'rejected' => 'Ditolak',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Teknisi')
                            ->disabled()
                            ->rows(3),
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Catatan Verifikasi')
                            ->rows(3),
                    ]),
                Forms\Components\Section::make('Foto Inspeksi')
                    ->schema([
                        Forms\Components\FileUpload::make('before_image')
                            ->label('Foto Sebelum Inspeksi')
                            ->image()
                            ->disabled()
                            ->columnSpan(1),
                        Forms\Components\FileUpload::make('after_image')
                            ->label('Foto Setelah Inspeksi')
                            ->image()
                            ->disabled()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Hanya tampilkan inspection yang status pending-verification atau completed (menunggu verifikasi supervisor)
                $query->whereIn("status", ['pending-verification', 'completed']);
                
                // Eager load relasi equipment dan technician
                $query->with(['equipment', 'technician']);
                
                // Debug query
                \Illuminate\Support\Facades\Log::info('VerificationResource query', [
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings()
                ]);
            })
            ->poll('2s') // Auto refresh setiap 2 detik untuk responsivitas yang lebih baik
            ->paginationPageOptions([5, 10, 25, 50])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Peralatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Teknisi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('inspection_date')
                    ->label('Tanggal Inspeksi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('before_image')
                    ->label('Foto Sebelum')
                    ->circular(),
                Tables\Columns\ImageColumn::make('after_image')
                    ->label('Foto Setelah')
                    ->circular(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verifikasi Inspeksi')
                    ->modalDescription('Apakah Anda yakin ingin memverifikasi inspeksi ini?')
                    ->modalSubmitActionLabel('Ya, Verifikasi')
                    ->form([
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Catatan Verifikasi'),
                    ])
                    ->action(function (Inspection $record, array $data) {
                        try {
                            // Log tindakan
                            \Illuminate\Support\Facades\Log::info('Mencoba memverifikasi inspeksi', [
                                'inspection_id' => $record->id,
                                'user_id' => auth()->id()
                            ]);
                            
                            // Periksa tipe kolom status di database
                            $columnType = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM inspections WHERE Field = 'status'")[0]->Type ?? '';
                            \Illuminate\Support\Facades\Log::info('Status column type', ['type' => $columnType]);
                            
                            // Jika tipe kolom adalah ENUM dan tidak mencakup 'verified', ubah menjadi VARCHAR
                            if (strpos(strtolower($columnType), 'enum') !== false && strpos($columnType, 'verified') === false) {
                                \Illuminate\Support\Facades\Log::warning('Fixing status column type', ['old_type' => $columnType]);
                                \Illuminate\Support\Facades\DB::statement("ALTER TABLE inspections MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
                            }
                            
                            // Gunakan direct query update untuk memastikan data berubah
                            $userId = auth()->id();
                            \Illuminate\Support\Facades\DB::transaction(function() use ($record, $data, $userId) {
                                // Gunakan prepared statement untuk memastikan semua nilai diescaped dengan benar
                                $sql = "UPDATE `inspections` SET 
                                      `status` = ?,
                                      `verification_notes` = ?,
                                      `verification_date` = NOW(),
                                      `verified_by` = ?,
                                      `updated_at` = NOW()
                                    WHERE `id` = ?";

                                \Illuminate\Support\Facades\DB::statement($sql, ['verified', $data['verification_notes'] ?? null, $userId, $record->id]);
                                
                                // Debug hasil update status
                                \Illuminate\Support\Facades\Log::info('Status after verify query', [
                                    'inspection_id' => $record->id,
                                    'status' => \Illuminate\Support\Facades\DB::table('inspections')->where('id', $record->id)->value('status')
                                ]);

                                // Update status maintenance menjadi completed (terverifikasi) jika ada
                                $maintenance = \App\Models\Maintenance::where('equipment_id', $record->equipment_id)
                                    ->where('technician_id', $record->technician_id)
                                    ->whereIn('status', ['in-progress', 'planned', 'pending'])
                                    ->first();

                                if ($maintenance) {
                                    // Update status maintenance
                                    $maintenance->status = Maintenance::STATUS_VERIFIED;  // verified berarti sudah terverifikasi
                                    $maintenance->approval_status = 'approved';
                                    $maintenance->approval_notes = $data['verification_notes'] ?? 'Terverifikasi oleh supervisor';
                                    $maintenance->approved_by = $userId;
                                    $maintenance->approval_date = now();
                                    $maintenance->actual_date = now();
                                    $maintenance->save();
                                    
                                    // Log perubahan maintenance
                                    \Illuminate\Support\Facades\Log::info('Maintenance terverifikasi', [
                                        'maintenance_id' => $maintenance->id,
                                        'status' => $maintenance->status
                                    ]);
                                }
                            });
                            
                            // Debug verifikasi
                            \Illuminate\Support\Facades\Log::info('Verifikasi selesai', [
                                'inspection_id' => $record->id,
                                'new_status' => \Illuminate\Support\Facades\DB::table('inspections')->where('id', $record->id)->value('status')
                            ]);

                            // Force flush cache
                            \Illuminate\Support\Facades\DB::connection()->flushQueryLog();
                            \Illuminate\Support\Facades\Cache::flush();
                            
                            // Kirim notifikasi dan redirect
                            Notification::make()
                                ->title('Inspection berhasil diverifikasi')
                                ->success()
                                ->send();
                                
                            // Redirect dengan JavaScript Window Location Reload untuk force refresh
                            return response()->json([
                                'success' => true,
                                'message' => 'Inspection berhasil diverifikasi',
                                'redirect' => self::getUrl('index'),
                                'script' => 'setTimeout(function(){ window.location.reload(true); }, 500);'
                            ]);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error verifikasi', [
                                'inspection_id' => $record->id,
                                'error' => $e->getMessage()
                            ]);
                            
                            Notification::make()
                                ->title('Gagal memverifikasi inspeksi')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Inspection $record) => in_array($record->status, ['pending-verification', 'completed'])),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Inspeksi')
                    ->modalDescription('Apakah Anda yakin ingin menolak inspeksi ini?')
                    ->modalSubmitActionLabel('Ya, Tolak')
                    ->form([
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Inspection $record, array $data) {
                        try {
                            // Log tindakan
                            \Illuminate\Support\Facades\Log::info('Mencoba menolak inspeksi', [
                                'inspection_id' => $record->id,
                                'user_id' => auth()->id()
                            ]);
                            
                            // Gunakan direct query update untuk memastikan data berubah
                            $userId = auth()->id();
                            \Illuminate\Support\Facades\DB::transaction(function() use ($record, $data, $userId) {
                                // Gunakan prepared statement untuk memastikan semua nilai diescaped dengan benar
                                $sql = "UPDATE `inspections` SET 
                                      `status` = ?,
                                      `verification_notes` = ?,
                                      `verification_date` = NOW(),
                                      `verified_by` = ?,
                                      `updated_at` = NOW()
                                    WHERE `id` = ?";

                                \Illuminate\Support\Facades\DB::statement($sql, ['rejected', $data['verification_notes'], $userId, $record->id]);
                                
                                // Update status maintenance menjadi rejected jika ada
                                $maintenance = \App\Models\Maintenance::where('equipment_id', $record->equipment_id)
                                    ->where('technician_id', $record->technician_id)
                                    ->whereIn('status', ['in-progress', 'planned', 'pending'])
                                    ->first();

                                if ($maintenance) {
                                    // Update status maintenance
                                    $maintenance->status = Maintenance::STATUS_REJECTED;
                                    $maintenance->approval_status = 'rejected';
                                    $maintenance->approval_notes = $data['verification_notes'];
                                    $maintenance->approved_by = $userId;
                                    $maintenance->approval_date = now();
                                    $maintenance->save();
                                    
                                    // Log perubahan maintenance
                                    \Illuminate\Support\Facades\Log::info('Maintenance ditolak', [
                                        'maintenance_id' => $maintenance->id,
                                        'status' => $maintenance->status
                                    ]);
                                }
                            });
                            
                            // Debug penolakan
                            \Illuminate\Support\Facades\Log::info('Penolakan selesai', [
                                'inspection_id' => $record->id,
                                'new_status' => \Illuminate\Support\Facades\DB::table('inspections')->where('id', $record->id)->value('status')
                            ]);

                            // Force flush cache
                            \Illuminate\Support\Facades\DB::connection()->flushQueryLog();
                            \Illuminate\Support\Facades\Cache::flush();
                            
                            // Kirim notifikasi dan redirect
                            Notification::make()
                                ->title('Inspection berhasil ditolak')
                                ->danger()
                                ->send();
                                
                            // Redirect dengan JavaScript Window Location Reload untuk force refresh
                            return response()->json([
                                'success' => true,
                                'message' => 'Inspection berhasil ditolak',
                                'redirect' => self::getUrl('index'),
                                'script' => 'setTimeout(function(){ window.location.reload(true); }, 500);'
                            ]);
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error penolakan', [
                                'inspection_id' => $record->id,
                                'error' => $e->getMessage()
                            ]);

                            Notification::make()
                                ->title('Gagal menolak inspeksi')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Inspection $record) => in_array($record->status, ['pending-verification', 'completed'])),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVerifications::route('/'),
            'view' => Pages\ViewVerification::route('/{record}'),
        ];
    }
    
    public static function infolist(Infolist $infolist): Infolist
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
}
