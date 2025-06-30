<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Filament\Resources\MaintenanceResource\RelationManagers;
use App\Models\Maintenance;
use App\Models\Equipment;
use App\Models\User;
use App\Models\Inspection;
use App\Exports\MaintenanceReportExport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Kelola Jadwal Maintenance';
    protected static ?string $navigationGroup = 'Administrator';
    protected static ?string $breadcrumb = 'Kelola Jadwal Maintenance';

    public static function canViewAny(): bool
    {
        // Teknisi dan admin dapat melihat halaman detail, 
        // tapi menu navigasi hanya ditampilkan untuk admin (diatur di shouldRegisterNavigation)
        $user = Auth::user();
        return $user && ($user->role === 'admin' || $user->role === 'technician');
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Hanya admin yang dapat melihat menu navigasi maintenance
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    public static function canView(Model $record): bool
    {
        $user = Auth::user();
        // Admin dapat melihat semua, teknisi hanya dapat melihat maintenance yang ditugaskan kepadanya
        return $user && (
            $user->role === 'admin' || 
            ($user->role === 'technician' && $record->technician_id === $user->id)
        );
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('equipment_id')
                    ->label('Equipment')
                    ->relationship('equipment', 'name')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $equipment = Equipment::find($state);
                            if ($equipment) {
                                $set('equipment_type', $equipment->type);
                                $set('priority', $equipment->priority);
                            }
                        }
                    })
                    ->required(),
                Forms\Components\DateTimePicker::make('schedule_date')
                    ->label('Jadwal Maintenance')
                    ->required(),
                Forms\Components\Select::make('technician_id')
                    ->label('Teknisi')
                    ->options(User::where('role', 'technician')->orWhere('role', '')->orWhereNull('role')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->helperText('Pilih teknisi yang akan ditugaskan untuk maintenance ini'),
                Forms\Components\Select::make('equipment_type')
                    ->label('Jenis Alat')
                    ->options([
                        'elektrik' => 'Elektrik',
                        'non-elektrik' => 'Non-Elektrik',
                    ])
                    ->disabled()
                    ->dehydrated(true)
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'merah' => 'Merah (Tinggi)',
                        'kuning' => 'Kuning (Sedang)',
                        'hijau' => 'Hijau (Rendah)',
                    ])
                    ->disabled()
                    ->dehydrated(true)
                    ->required(),
                Forms\Components\Select::make('maintenance_type')
                    ->label('Jenis Maintenance')
                    ->options([
                        'preventive' => 'Preventive',
                        'corrective' => 'Corrective',
                    ])
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Reported / Pending Approval',
                        'planned' => 'Planned',
                        'assigned' => 'Assigned',
                        'in-progress' => 'In Progress',
                        'pending-verification' => 'Pending Verification',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\TextInput::make('cost')
                    ->label('Biaya')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->required(),
                Forms\Components\DateTimePicker::make('actual_date')
                    ->label('Tanggal Aktual')
                    ->visible(function (Forms\Get $get) {
                        return in_array($get('status'), ['completed', 'verified']);
                    }),
                Forms\Components\TextInput::make('duration')
                    ->label('Durasi (menit)')
                    ->numeric()
                    ->visible(function (Forms\Get $get) {
                        return in_array($get('status'), ['completed', 'verified']);
                    }),
                Forms\Components\TextInput::make('location_lat')
                    ->label('Latitude')
                    ->hidden(function (Forms\Get $get) {
                        return !in_array($get('status'), ['completed', 'verified']);
                    }),
                Forms\Components\TextInput::make('location_lng')
                    ->label('Longitude')
                    ->hidden(function (Forms\Get $get) {
                        return !in_array($get('status'), ['completed', 'verified']);
                    }),
                Forms\Components\FileUpload::make('before_image')
                    ->label('Foto Sebelum Maintenance')
                    ->directory('maintenance-before')
                    ->required(),
                Forms\Components\FileUpload::make('after_image')
                    ->label('Foto Setelah Maintenance')
                    ->directory('maintenance-after')
                    ->visible(function (Forms\Get $get) {
                        return in_array($get('status'), ['completed', 'verified']);
                    }),
                Forms\Components\CheckboxList::make('checklist')
                    ->label('Checklist Digital')
                    ->options([
                        'inspeksi_visual' => 'Inspeksi Visual',
                        'uji_fungsi' => 'Uji Fungsi',
                        'pembersihan' => 'Pembersihan',
                        'penggantian_komponen' => 'Penggantian Komponen',
                        'kalibrasi' => 'Kalibrasi',
                        'pengujian_keamanan' => 'Pengujian Keamanan',
                    ])
                    ->columns(2)
                    ->required(),
                Forms\Components\Select::make('approval_status')
                    ->label('Status Approval')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->disabled(function () {
                        $user = Auth::user();
                        return !($user && in_array($user->role, ['admin', 'supervisor']));
                    })
                    ->visible(function (Forms\Get $get) {
                        return in_array($get('status'), ['completed', 'verified']);
                    }),
                Forms\Components\Textarea::make('approval_notes')
                    ->label('Catatan Approval')
                    ->visible(function (Forms\Get $get) {
                        return in_array($get('status'), ['completed', 'verified']);
                    }),
                Forms\Components\DateTimePicker::make('next_service_date')
                    ->label('Jadwal Service Berikutnya')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $isAdmin = auth()->user()->role === 'admin';
        
        // Jika user adalah teknisi, filter data hanya untuk maintenance yang ditugaskan kepadanya
        $table = $table
            ->modifyQueryUsing(function ($query) use ($isAdmin) {
                if (!$isAdmin) {
                    $query->where('technician_id', auth()->id());
                } else {
                    // Untuk admin, hanya tampilkan data yang sudah ditugaskan ke teknisi
                    $query->whereNotNull('technician_id');
                }
                
                return $query;
            });
            
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('schedule_date')
                    ->label('Jadwal')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('equipment_type')
                    ->label('Jenis Alat')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'merah' => 'danger',
                        'kuning' => 'warning',
                        'hijau' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'planned',
                        'secondary' => 'assigned',
                        'info' => 'in-progress',
                        'purple' => 'pending-verification',
                        'green' => 'verified',
                        'danger' => 'rejected',
                        'success' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending Approval',
                        'planned' => 'Planned',
                        'assigned' => 'Assigned',
                        'in-progress' => 'In Progress',
                        'pending-verification' => 'Pending Verification',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Approval',
                        'planned' => 'Planned',
                        'assigned' => 'Assigned',
                        'in-progress' => 'In Progress',
                        'pending-verification' => 'Pending Verification',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('equipment_type')
                    ->label('Jenis Alat')
                    ->options([
                        'elektrik' => 'Elektrik',
                        'non-elektrik' => 'Non-Elektrik',
                    ]),
                Tables\Filters\SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'merah' => 'Merah',
                        'kuning' => 'Kuning',
                        'hijau' => 'Hijau',
                    ]),
            ])
            ->headerActions([
                // Export actions removed - export functionality only available on reports page
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
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
                    ->action(function (Maintenance $record, array $data) {
                        $record->technician_id = $data['technician_id'];
                        $record->save();
                        
                        // Update role user menjadi teknisi jika belum
                        $technician = User::find($data['technician_id']);
                        if ($technician && $technician->role !== 'technician') {
                            $technician->role = 'technician';
                            $technician->save();
                        }
                        
                        // Buat inspection otomatis saat teknisi ditugaskan
                        $inspection = Inspection::where('equipment_id', $record->equipment_id)
                            ->where('technician_id', $record->technician_id)
                            ->first();
                            
                        if (!$inspection) {
                            $inspection = new Inspection();
                            $inspection->equipment_id = $record->equipment_id;
                            $inspection->technician_id = $record->technician_id;
                            $inspection->inspection_date = $record->schedule_date;
                            $inspection->schedule_date = $record->schedule_date;
                            $inspection->status = 'pending';
                            $inspection->notes = "Dibuat otomatis dari penugasan teknisi: " . $record->schedule_date->format('d M Y H:i');
                            $inspection->save();
                        }
                        
                        Notification::make()
                            ->title('Teknisi berhasil ditugaskan')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => auth()->user()->role === 'admin'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TechnicianRelationManager::class,
            RelationManagers\InspectionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
            'view' => Pages\ViewMaintenance::route('/{record}'),
        ];
    }

    public static function afterCreate($record)
    {
        // Buat inspection baru otomatis saat maintenance dibuat jika ada teknisi yang ditugaskan
        if ($record->technician_id) {
            // Cek apakah sudah ada inspection untuk kombinasi equipment dan teknisi ini
            $existingInspection = Inspection::where('equipment_id', $record->equipment_id)
                ->where('technician_id', $record->technician_id)
                ->first();
                
            if (!$existingInspection) {
                $inspection = new Inspection();
                $inspection->equipment_id = $record->equipment_id;
                $inspection->technician_id = $record->technician_id;
                $inspection->inspection_date = $record->schedule_date;
                $inspection->schedule_date = $record->schedule_date;
                $inspection->status = 'pending';
                $inspection->notes = "Dibuat otomatis dari jadwal maintenance: " . $record->schedule_date->format('d M Y H:i');
                $inspection->save();
            }

            Notification::make()
                ->title('Reminder Maintenance')
                ->body('Anda memiliki jadwal maintenance untuk ' . ($record->equipment ? $record->equipment->name : 'peralatan'))
                ->success()
                ->send();
        }
    }

    public static function afterUpdate($record)
    {
        // Perbarui inspection terkait jika data maintenance diubah
        if ($record->isDirty('equipment_id') || $record->isDirty('technician_id') || $record->isDirty('schedule_date')) {
            // Cari inspection terkait
            $inspection = Inspection::where('equipment_id', $record->getOriginal('equipment_id'))
                ->where('technician_id', $record->getOriginal('technician_id'))
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($inspection) {
                $inspection->equipment_id = $record->equipment_id;
                $inspection->technician_id = $record->technician_id;
                $inspection->inspection_date = $record->schedule_date;
                $inspection->schedule_date = $record->schedule_date;
                $inspection->notes = "Diperbarui dari jadwal maintenance: " . $record->schedule_date->format('d M Y H:i');
                $inspection->save();
            } else {
                // Jika tidak ada inspection yang ditemukan, buat yang baru
                $inspection = new Inspection();
                $inspection->equipment_id = $record->equipment_id;
                $inspection->technician_id = $record->technician_id;
                $inspection->inspection_date = $record->schedule_date;
                $inspection->schedule_date = $record->schedule_date;
                $inspection->status = 'pending';
                $inspection->notes = "Dibuat otomatis dari jadwal maintenance yang diperbarui: " . $record->schedule_date->format('d M Y H:i');
                $inspection->save();
            }
        }
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Status Inspeksi')
                    ->visible(function ($record) {
                        return $record->hasInspection();
                    })
                    ->schema([
                        TextEntry::make('latestInspection.status')
                            ->label('Status Inspeksi')
                            ->badge()
                            ->color(function ($state) {
                                return match ($state) {
                                    'pending' => 'warning',
                                    'completed' => 'success',
                                    'verified' => 'primary',
                                    'rejected' => 'danger',
                                    default => 'gray',
                                };
                            }),
                        TextEntry::make('latestInspection.inspection_date')
                            ->label('Tanggal Inspeksi')
                            ->dateTime(),
                        TextEntry::make('latestInspection.technician.name')
                            ->label('Teknisi'),
                        TextEntry::make('latestInspection.notes')
                            ->label('Catatan'),
                        ImageEntry::make('latestInspection.before_image')
                            ->label('Foto Sebelum'),
                        ImageEntry::make('latestInspection.after_image')
                            ->label('Foto Setelah')
                            ->visible(function ($record) {
                                return $record->latestInspection && $record->latestInspection->after_image;
                            }),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Admin hanya melihat maintenance yang sudah ditugaskan ke teknisi
        if (auth()->user() && auth()->user()->role === 'admin') {
            $query->whereNotNull('technician_id');
        }
        
        return $query;
    }
}
