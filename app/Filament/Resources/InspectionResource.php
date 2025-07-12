<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InspectionResource\Pages;
use App\Models\Inspection;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Kelola Inspection';
    protected static ?string $navigationGroup = 'Teknisi';
    protected static ?int $navigationSort = 1;
    protected static ?string $breadcrumb = 'Kelola Inspection';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        
        // Jika bukan teknisi, kembalikan false
        if (!$user || !$user->isTechnician()) {
            return false;
        }
        
        // Jika teknisi, cek apakah ada inspection yang ditugaskan kepadanya
        // yang belum selesai (tidak verified atau rejected)
        $hasInspections = Inspection::where('technician_id', $user->id)
            ->whereNotIn('status', ['verified', 'rejected'])
            ->exists();
            
        return $hasInspections;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canViewAny();
    }
    
    public static function canView(Model $record): bool
    {
        $user = auth()->user();
        
        // Admin dan supervisor bisa melihat semua
        if ($user && ($user->role === 'admin' || $user->role === 'supervisor')) {
            return true;
        }
        
        // Teknisi hanya bisa melihat inspection yang ditugaskan kepadanya
        if ($user && $user->isTechnician()) {
            return $record->technician_id === $user->id;
        }
        
        return false;
    }
    
    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        
        // Admin bisa edit semua
        if ($user && $user->role === 'admin') {
            return true;
        }
        
        // Supervisor tidak bisa edit
        if ($user && $user->role === 'supervisor') {
            return false;
        }
        
        // Teknisi hanya bisa edit inspection yang ditugaskan kepadanya
        // Dan statusnya belum verified atau rejected
        if ($user && $user->isTechnician()) {
            return $record->technician_id === $user->id && 
                   !in_array($record->status, ['verified', 'rejected']);
        }
        
        return false;
    }
    
    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        
        // Hanya admin yang bisa delete
        return $user && $user->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isTechnician = $user && $user->isTechnician();
        $isSupervisor = $user && $user->role === 'supervisor';

        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
            ->schema([
                Forms\Components\Select::make('equipment_id')
                    ->label('Peralatan')
                    ->options(Equipment::all()->pluck('name', 'equipment_id'))
                    ->searchable()
                    ->disabled($isSupervisor)
                    ->required(),
                Forms\Components\DateTimePicker::make('inspection_date')
                    ->label('Tanggal Inspeksi')
                    ->default(now())
                    ->disabled($isSupervisor)
                    ->required(),
                Forms\Components\Select::make('technician_id')
                    ->label('Teknisi')
                    ->relationship('technician', 'name')
                    ->searchable()
                    ->disabled($isSupervisor)
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Belum Selesai',
                        'in-progress' => 'Sedang Dikerjakan',
                        'pending-verification' => 'Menunggu Verifikasi',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending')
                    ->disabled(function() use ($isTechnician, $isSupervisor) {
                        if ($isSupervisor) {
                            return false;
                        }
                        return false;
                    })
                    ->required(),
                ]),

                Forms\Components\Section::make('Detail Inspection')
                ->schema([
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->disabled($isSupervisor)
                    ->rows(3),
                Forms\Components\FileUpload::make('before_image')
                    ->label('Foto Sebelum Inspeksi')
                    ->image()
                    ->directory('inspections/before')
                    ->disk('public')
                    ->visibility('public')
                    ->maxSize(5120) // 5MB
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('600')
                    ->imageResizeTargetHeight('600')
                    ->disabled($isSupervisor)
                    ->required(),
                Forms\Components\FileUpload::make('after_image')
                    ->label('Foto Setelah Inspeksi')
                    ->image()
                    ->directory('inspections/after')
                    ->disk('public')
                    ->visibility('public')
                    ->maxSize(5120) // 5MB
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('1:1')
                    ->imageResizeTargetWidth('600')
                    ->imageResizeTargetHeight('600')
                    ->disabled($isSupervisor)
                    ->required(fn (Forms\Get $get) => $get('status') === 'completed')
                    ->visible(fn (Forms\Get $get) => in_array($get('status'), ['completed', 'verified', 'rejected'])),
                Forms\Components\CheckboxList::make('checklist')
                    ->label('Checklist Inspeksi')
                    ->options([
                        'inspeksi_visual' => 'Inspeksi Visual',
                        'uji_fungsi' => 'Uji Fungsi',
                        'pembersihan' => 'Pembersihan',
                        'pengecekan_komponen' => 'Pengecekan Komponen',
                        'pengujian_keamanan' => 'Pengujian Keamanan',
                    ])
                    ->disabled($isSupervisor)
                    ->columns(2),
                ]),

                Forms\Components\Section::make('Lokasi')
                ->schema([
                Forms\Components\TextInput::make('location')
                    ->label('Lokasi')
                    ->disabled($isSupervisor)
                    ->maxLength(255),
                Forms\Components\Hidden::make('technician_id')
                    ->default(fn () => $isTechnician ? Auth::id() : null),
                Forms\Components\Hidden::make('location_lat'),
                Forms\Components\Hidden::make('location_lng'),
                Forms\Components\Hidden::make('location_timestamp'),
                Forms\Components\DateTimePicker::make('completion_date')
                    ->label('Tanggal Penyelesaian')
                    ->visible(true)
                    ->disabled($isSupervisor)
                    ->default(now()),
                ]),

                Forms\Components\Section::make('Verifikasi')
                ->schema([
                    Forms\Components\Textarea::make('verification_notes')
                        ->label('Catatan Verifikasi')
                        ->visible(fn () => $isSupervisor || auth()->user()->role === 'admin')
                        ->disabled(!$isSupervisor)
                        ->rows(3),
                ]),

                Forms\Components\Section::make('Maintenance Terkait')
                ->visible(function ($record) {
                    return $record && $record->getLatestMaintenanceAttribute();
                })
                ->schema([
                    Forms\Components\Placeholder::make('maintenance_info')
                        ->label('Informasi Maintenance')
                        ->content(function ($record) {
                            $maintenance = $record->getLatestMaintenanceAttribute();
                            if (!$maintenance) return 'Tidak ada maintenance terkait';

                            // Pastikan semua properti ada sebelum diakses
                            $id = $maintenance->id ?? 'N/A';
                            $jadwal = isset($maintenance->schedule_date) ? $maintenance->schedule_date->format('d-m-Y H:i') : 'N/A';
                            $status = $maintenance->status ?? 'N/A';
                            $prioritas = $maintenance->priority ?? 'N/A';

                            return "ID: {$id}\nJadwal: {$jadwal}\nStatus: {$status}\nPrioritas: {$prioritas}";
                        }),
                    Forms\Components\ViewField::make('maintenance_link')
                        ->label('Lihat Detail')
                        ->view('filament.components.maintenance-link'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isTechnician = $user && $user->isTechnician();
        $isSupervisor = $user && $user->role === 'supervisor';

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($isTechnician, $isSupervisor, $user) {
                if ($isTechnician) {
                    // Teknisi hanya bisa melihat inspeksi yang ditugaskan kepadanya
                    // Dan hanya yang belum diverifikasi atau ditolak
                    $query->where('technician_id', $user->id)
                          ->whereNotIn('status', ['verified', 'rejected']);
                } elseif ($isSupervisor) {
                    // Supervisor bisa melihat semua inspeksi
                    // Tidak perlu filter tambahan
                } else {
                    // Admin bisa melihat semua inspeksi
                    // Tidak perlu filter tambahan
                }
                
                // Filter untuk hanya menampilkan inspection yang terkait dengan maintenance
                // yang statusnya sesuai dan sudah memiliki teknisi
                $query->where(function ($q) {
                    // Inspection dengan maintenance_id yang valid
                    $q->whereHas('maintenance', function ($mq) {
                        $mq->whereIn('status', ['assigned', 'in-progress', 'pending-verification'])
                           ->whereNotNull('technician_id');
                    })
                    // ATAU inspection tanpa maintenance_id tapi dengan technician_id
                    ->orWhere(function ($q2) {
                        $q2->whereNull('maintenance_id')
                           ->whereNotNull('technician_id');
                    });
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Peralatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('inspection_date')
                    ->label('Tanggal Inspeksi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                // Kolom status dihilangkan berdasarkan permintaan
                Tables\Columns\TextColumn::make('completion_date')
                    ->label('Tanggal Penyelesaian')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('maintenance.id')
                    ->label('ID Maintenance')
                    ->url(fn (Inspection $record) => $record->maintenance_id ? 
                        MaintenanceResource::getUrl('view', ['record' => $record->maintenance_id]) : null)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->toggleable(),
                Tables\Columns\ImageColumn::make('before_image')
                    ->label('Foto Sebelum')
                    ->circular()
                    ->toggleable(),
                Tables\Columns\ImageColumn::make('after_image')
                    ->label('Foto Setelah')
                    ->circular()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'in-progress',
                        'purple' => 'pending-verification',
                        'green' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Belum Selesai',
                        'in-progress' => 'Sedang Dikerjakan',
                        'pending-verification' => 'Menunggu Verifikasi',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('equipment_id')
                    ->label('Equipment')
                    ->relationship('equipment', 'name'),
                Tables\Filters\SelectFilter::make('technician_id')
                    ->label('Teknisi')
                    ->relationship('technician', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Belum Selesai',
                        'in-progress' => 'Sedang Dikerjakan',
                        'pending-verification' => 'Menunggu Verifikasi',
                        'verified' => 'Terverifikasi',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                // ViewAction diganti dengan Action Tugaskan Teknisi
                Action::make('assign_technician')
                    ->label('Tugaskan Teknisi')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('technician_id')
                            ->label('Teknisi')
                            ->options(User::where('role', 'technician')->orWhere('role', '')->orWhereNull('role')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Inspection $record, array $data) {
                        $record->technician_id = $data['technician_id'];
                        $record->save();

                        // Update role user menjadi teknisi jika belum
                        $technician = User::find($data['technician_id']);
                        if ($technician && $technician->role !== 'technician') {
                            $technician->role = 'technician';
                            $technician->save();
                        }

                        Notification::make()
                            ->title('Teknisi berhasil ditugaskan')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => auth()->user()->role === 'admin'),
                Action::make('upload_photos')
                    ->label('Upload Inspeksi')
                    ->icon('heroicon-o-camera')
                    ->color('primary')
                    ->url(fn (Inspection $record) => static::getUrl('upload-photos', ['record' => $record->id]))
                    ->visible(fn (Inspection $record) => auth()->user()->isTechnician() && 
                        $record->technician_id === auth()->id() && 
                        !in_array($record->status, ['verified', 'rejected'])),
                Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Catatan Verifikasi'),
                    ])
                    ->action(function (Inspection $record, array $data) {
                        // Gunakan method safeVerify dari trait StatusSafety
                        $record->safeVerify($data['verification_notes'] ?? null, auth()->id());

                        // Update status maintenance menjadi verified (terverifikasi) jika ada
                        $maintenance = Maintenance::where('equipment_id', $record->equipment_id)
                            ->where('technician_id', $record->technician_id)
                            ->whereIn('status', ['in-progress', 'planned', 'pending'])
                            ->first();

                        if ($maintenance) {
                            $maintenance->status = Maintenance::STATUS_VERIFIED; // verified berarti sudah diverifikasi oleh supervisor
                            $maintenance->approval_status = 'approved';
                            $maintenance->approval_notes = $data['verification_notes'] ?? 'Terverifikasi oleh supervisor';
                            $maintenance->approved_by = auth()->id();
                            $maintenance->approval_date = now();
                            $maintenance->actual_date = now();
                            $maintenance->save();
                        }

                        Notification::make()
                            ->title('Inspection berhasil diverifikasi')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Inspection $record) => auth()->user()->role === 'supervisor' && $record->status === 'completed'),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Inspection $record, array $data) {
                        // Gunakan metode safeReject dari trait StatusSafety
                        $record->safeReject($data['verification_notes'], auth()->id());

                        // Update status maintenance menjadi rejected jika ada
                        $maintenance = Maintenance::where('equipment_id', $record->equipment_id)
                            ->where('technician_id', $record->technician_id)
                            ->whereIn('status', ['in-progress', 'planned', 'pending'])
                            ->first();

                        if ($maintenance) {
                            $maintenance->status = Maintenance::STATUS_REJECTED;
                            $maintenance->approval_status = 'rejected';
                            $maintenance->approval_notes = $data['verification_notes'];
                            $maintenance->approved_by = auth()->id();
                            $maintenance->approval_date = now();
                            $maintenance->save();
                        }

                        Notification::make()
                            ->title('Inspection ditolak')
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (Inspection $record) => auth()->user()->role === 'supervisor' && $record->status === 'completed'),
                Action::make('returnToPending')
                    ->label('Kembalikan ke Pending')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Kembalikan Status Inspeksi')
                    ->modalDescription('Apakah Anda yakin ingin mengembalikan status inspeksi ini ke "Belum Selesai"? Tindakan ini akan memungkinkan teknisi untuk mengedit dan mengupload ulang foto.')
                    ->modalSubmitActionLabel('Ya, Kembalikan Status')
                    ->action(function (Inspection $record) {
                        // Gunakan metode safeReturnToPending dari trait StatusSafety
                        $record->safeReturnToPending();

                        Notification::make()
                            ->title('Status inspeksi dikembalikan')
                            ->body('Status inspeksi telah dikembalikan ke "Belum Selesai".')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Inspection $record) => auth()->user()->role === 'supervisor' && $record->status === 'completed'),
                // Tombol Delete dan Lihat Maintenance dihilangkan
            ])
            ->bulkActions([
                // Bulk actions dihapus
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
            'index' => Pages\ListInspections::route('/'),
            'create' => Pages\CreateInspection::route('/create'),
            'view' => Pages\ViewInspection::route('/{record}'),
            'upload-photos' => Pages\UploadInspectionPhotos::route('/{record}/upload-inspeksi'),
        ];
    }
}
