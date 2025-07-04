<?php

namespace App\Filament\Resources\InspectionResource\Pages;

use App\Filament\Resources\InspectionResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\Inspection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UploadInspectionPhotos extends EditRecord
{
    protected static string $resource = InspectionResource::class;

    protected static string $view = 'filament.resources.inspection-resource.pages.upload-inspection-photos';
    
    public function getTitle(): string
    {
        return 'Upload Inspeksi';
    }
    
    // Menghapus tombol default
    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetStatus')
                ->label('Reset Status')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reset Status Inspeksi')
                ->modalDescription('Apakah Anda yakin ingin mengubah status inspeksi kembali menjadi "Belum Selesai"?')
                ->modalSubmitActionLabel('Ya, Reset Status')
                ->modalCancelActionLabel('Batal')
                ->action(function () {
                    $this->record->status = 'pending';
                    $this->record->completion_date = null;
                    $this->record->save();
                    
                    $this->fillForm();
                    
                    Notification::make()
                        ->success()
                        ->title('Status berhasil direset')
                        ->body('Status inspeksi telah diubah kembali menjadi "Belum Selesai".')
                        ->send();
                })
                ->visible(fn () => $this->record->status === 'completed' && !$this->record->isVerified()),
                
            Action::make('removeBeforeImage')
                ->label('Hapus Foto Sebelum')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Foto Sebelum Inspeksi')
                ->modalDescription('Apakah Anda yakin ingin menghapus foto sebelum inspeksi?')
                ->modalSubmitActionLabel('Ya, Hapus Foto')
                ->modalCancelActionLabel('Batal')
                ->action(function () {
                    if ($this->record->before_image) {
                        Storage::disk('public')->delete($this->record->before_image);
                        $this->record->before_image = null;
                        $this->record->save();
                        
                        $this->fillForm();
                        
                        Notification::make()
                            ->success()
                            ->title('Foto berhasil dihapus')
                            ->body('Foto sebelum inspeksi telah dihapus.')
                            ->send();
                    }
                })
                ->visible(fn () => !empty($this->record->before_image) && !$this->record->isVerified()),
                
            Action::make('removeAfterImage')
                ->label('Hapus Foto Sesudah')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Foto Sesudah Inspeksi')
                ->modalDescription('Apakah Anda yakin ingin menghapus foto sesudah inspeksi?')
                ->modalSubmitActionLabel('Ya, Hapus Foto')
                ->modalCancelActionLabel('Batal')
                ->action(function () {
                    if ($this->record->after_image) {
                        Storage::disk('public')->delete($this->record->after_image);
                        $this->record->after_image = null;
                        $this->record->save();
                        
                        $this->fillForm();
                        
                        Notification::make()
                            ->success()
                            ->title('Foto berhasil dihapus')
                            ->body('Foto sesudah inspeksi telah dihapus.')
                            ->send();
                    }
                })
                ->visible(fn () => !empty($this->record->after_image) && !$this->record->isVerified()),
        ];
    }
    
    // Menggunakan metode bawaan EditRecord untuk menangani update record
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Update hanya field yang diizinkan untuk teknisi
        $oldBeforeImage = $record->before_image;
        $oldAfterImage = $record->after_image;
        
        $record->before_image = $data['before_image'] ?? $record->before_image;
        $record->after_image = $data['after_image'] ?? $record->after_image;
        $record->status = $data['status'];
        $record->notes = $data['notes'];
        
        // Update lokasi
        $record->location = $data['location'] ?? $record->location;
        $record->location_lat = $data['location_lat'] ?? $record->location_lat;
        $record->location_lng = $data['location_lng'] ?? $record->location_lng;
        $record->location_timestamp = $data['location_timestamp'] ?? now();
        
        // Cari maintenance terkait berdasarkan maintenance_id atau kombinasi equipment_id dan technician_id
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
        
        // Deteksi perubahan foto dan update status
        $beforeImageAdded = empty($oldBeforeImage) && !empty($record->before_image);
        $afterImageAdded = empty($oldAfterImage) && !empty($record->after_image);
        
        // Jika foto sebelum baru ditambahkan, ubah status menjadi in-progress
        if ($beforeImageAdded) {
            $record->status = 'in-progress';
            
            // Update status maintenance jika ada
            if ($maintenance) {
                $maintenance->status = \App\Models\Maintenance::STATUS_IN_PROGRESS;
                $maintenance->before_image = $record->before_image;
                $maintenance->save();
                
                \Illuminate\Support\Facades\Log::info('Maintenance status updated to in-progress after before_image upload', [
                    'maintenance_id' => $maintenance->id,
                    'inspection_id' => $record->id
                ]);
            }
        }
        
        // Jika foto sesudah baru ditambahkan, ubah status menjadi pending-verification
        if ($afterImageAdded) {
            $record->status = 'pending-verification';
            $record->completion_date = now();
            
            // Update status maintenance jika ada
            if ($maintenance) {
                $maintenance->status = \App\Models\Maintenance::STATUS_PENDING_VERIFICATION;
                $maintenance->after_image = $record->after_image;
                $maintenance->save();
                
                \Illuminate\Support\Facades\Log::info('Maintenance status updated to pending-verification after after_image upload', [
                    'maintenance_id' => $maintenance->id,
                    'inspection_id' => $record->id
                ]);
            }
        }
        
        // Update status berdasarkan pilihan user jika tidak ada perubahan foto
        if (!$beforeImageAdded && !$afterImageAdded) {
            // Update tanggal penyelesaian berdasarkan status
            if ($data['status'] === 'pending-verification') {
                $record->completion_date = $data['completion_date'] ?? now();
                
                // Update status maintenance jika ada
                if ($maintenance) {
                    $maintenance->status = \App\Models\Maintenance::STATUS_PENDING_VERIFICATION;
                    $maintenance->save();
                    
                    \Illuminate\Support\Facades\Log::info('Maintenance status updated to pending-verification', [
                        'maintenance_id' => $maintenance->id,
                        'inspection_id' => $record->id
                    ]);
                }
            } else if ($data['status'] === 'in-progress') {
                // Update status maintenance jika ada
                if ($maintenance) {
                    $maintenance->status = \App\Models\Maintenance::STATUS_IN_PROGRESS;
                    $maintenance->save();
                    
                    \Illuminate\Support\Facades\Log::info('Maintenance status updated to in-progress', [
                        'maintenance_id' => $maintenance->id,
                        'inspection_id' => $record->id
                    ]);
                }
            } else {
                $record->completion_date = null;
            }
        }
        
        $record->save();
        
        return $record;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Foto berhasil diupload')
            ->body('Data inspeksi berhasil diperbarui.');
    }
    
    protected function authorizeAccess(): void
    {
        // Verify that the current user is the assigned technician
        if (auth()->id() !== $this->record->technician_id) {
            Notification::make()
                ->danger()
                ->title('Akses ditolak')
                ->body('Anda tidak memiliki akses untuk mengupload foto pada inspeksi ini.')
                ->send();
                
            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }
        
        // Jika inspeksi sudah diverifikasi, tidak boleh diedit lagi
        if ($this->record->isVerified() || $this->record->isRejected()) {
            Notification::make()
                ->warning()
                ->title('Tidak dapat diedit')
                ->body('Inspeksi yang sudah diverifikasi atau ditolak tidak dapat diedit lagi.')
                ->send();
                
            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }
        
        parent::authorizeAccess();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Status Inspeksi')
                    ->description('Pilih status yang sesuai dengan kondisi inspeksi saat ini.')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Belum Selesai',
                                'in-progress' => 'Sedang Dikerjakan',
                                'pending-verification' => 'Menunggu Verifikasi',
                            ])
                            ->default(fn () => $this->record->status)
                            ->required()
                            ->reactive()
                            ->helperText('Pilih "Menunggu Verifikasi" jika inspeksi sudah selesai dan menunggu verifikasi supervisor.'),
                            
                        Forms\Components\DateTimePicker::make('completion_date')
                            ->label('Tanggal Penyelesaian')
                            ->default(fn () => $this->record->completion_date ?? now())
                            ->required(fn ($get) => $get('status') === 'completed')
                            ->visible(fn ($get) => $get('status') === 'completed')
                            ->helperText('Tanggal dan waktu saat inspeksi selesai dilakukan.'),
                    ]),
                
                Forms\Components\Section::make('Foto Inspeksi')
                    ->description('Upload foto sebelum dan sesudah inspeksi.')
                    ->schema([
                        FileUpload::make('before_image')
                            ->label('Foto Sebelum Inspeksi')
                            ->image()
                            ->directory('inspections/before')
                            ->visibility('public')
                            ->disk('public')
                            ->maxSize(5120) // 5MB
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('600')
                            ->imageResizeTargetHeight('600')
                            ->required(fn () => empty($this->record->before_image))
                            ->helperText('Upload foto kondisi awal sebelum inspeksi dilakukan.'),
                        
                        FileUpload::make('after_image')
                            ->label('Foto Sesudah Inspeksi')
                            ->image()
                            ->directory('inspections/after')
                            ->visibility('public')
                            ->disk('public')
                            ->maxSize(5120) // 5MB
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('1:1')
                            ->imageResizeTargetWidth('600')
                            ->imageResizeTargetHeight('600')
                            ->required(fn ($get) => $get('status') === 'pending-verification' && empty($this->record->after_image))
                            ->visible(fn ($get) => $get('status') === 'pending-verification')
                            ->helperText('Upload foto kondisi setelah inspeksi selesai dilakukan.'),
                    ]),
                
                Forms\Components\Section::make('Lokasi')
                    ->description('Masukkan lokasi inspeksi.')
                    ->schema([
                        Forms\Components\TextInput::make('location')
                            ->label('Lokasi')
                            ->default(fn () => $this->record->location)
                            ->maxLength(255)
                            ->helperText('Masukkan lokasi inspeksi (contoh: Gedung Terminal, Lantai 2).'),
                        
                        Forms\Components\Hidden::make('location_lat')
                            ->default(fn () => $this->record->location_lat),
                        
                        Forms\Components\Hidden::make('location_lng')
                            ->default(fn () => $this->record->location_lng),
                        
                        Forms\Components\Hidden::make('location_timestamp')
                            ->default(fn () => $this->record->location_timestamp ?? now()),
                    ]),
                
                Forms\Components\Section::make('Catatan')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan Inspeksi')
                            ->default(fn () => $this->record->notes)
                            ->rows(3)
                            ->helperText('Tambahkan catatan atau temuan selama inspeksi.'),
                    ]),
            ]);
    }
    
    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user() && auth()->user()->isTechnician();
    }
} 