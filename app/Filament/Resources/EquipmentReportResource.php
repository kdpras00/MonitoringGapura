<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentReportResource\Pages;
use App\Models\EquipmentReport;
use App\Models\Equipment;
use App\Models\User;
use App\Filament\Resources\MaintenanceResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EquipmentReportResource extends Resource
{
    protected static ?string $model = EquipmentReport::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Laporan Kerusakan';
    protected static ?string $modelLabel = 'Laporan Kerusakan';
    protected static ?string $pluralModelLabel = 'Laporan Kerusakan';
    protected static ?string $navigationGroup = 'Administrator';
    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        // Hanya tampilkan badge jika user adalah admin atau supervisor
        if (Auth::user()->role === 'admin' || Auth::user()->role === 'supervisor') {
            return static::getModel()::where('status', EquipmentReport::STATUS_PENDING)->count();
        }
        return null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && ($user->role === 'admin' || $user->role === 'supervisor' || $user->role === 'operator');
    }
    
    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        
        // Laporan yang sudah disetujui/ditolak tidak bisa diedit
        if ($record->status !== EquipmentReport::STATUS_PENDING) {
            return false;
        }
        
        // Admin & Supervisor bisa edit semua laporan yang masih pending
        if ($user && ($user->role === 'admin' || $user->role === 'supervisor')) {
            return true;
        }
        
        // Operator hanya bisa edit laporan yang mereka buat sendiri
        if ($user && $user->role === 'operator') {
            return $user->id === $record->reporter_id;
        }
        
        return false;
    }
    
    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();
        
        // Laporan yang sudah disetujui/ditolak tidak bisa dihapus
        if ($record->status !== EquipmentReport::STATUS_PENDING) {
            return false;
        }
        
        // Admin bisa hapus laporan
        if ($user && $user->role === 'admin') {
            return true;
        }
        
        return false;
    }
    
    public static function form(Form $form): Form
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        $isSupervisor = $user && $user->role === 'supervisor';
        
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kerusakan')
            ->schema([
                Forms\Components\Select::make('equipment_id')
                            ->label('Equipment')
                            ->options(Equipment::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->disabled(fn ($record) => $record && $record->status !== EquipmentReport::STATUS_PENDING),
                        
                        Forms\Components\Select::make('priority')
                            ->label('Prioritas')
                    ->options([
                                EquipmentReport::PRIORITY_LOW => 'Rendah',
                                EquipmentReport::PRIORITY_MEDIUM => 'Sedang',
                                EquipmentReport::PRIORITY_HIGH => 'Tinggi',
                    ])
                            ->default(EquipmentReport::PRIORITY_MEDIUM)
                            ->required()
                            ->disabled(fn ($record) => $record && $record->status !== EquipmentReport::STATUS_PENDING),
                        
                        Forms\Components\RichEditor::make('issue_description')
                            ->label('Deskripsi Kerusakan')
                            ->required()
                            ->disabled(fn ($record) => $record && $record->status !== EquipmentReport::STATUS_PENDING)
                            ->columnSpan(2),
                            
                        Forms\Components\FileUpload::make('issue_image')
                            ->label('Foto Kerusakan')
                            ->image()
                            ->directory('equipment-reports')
                            ->maxSize(5120)
                            ->disabled(fn ($record) => $record && $record->status !== EquipmentReport::STATUS_PENDING)
                            ->columnSpan(2),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Persetujuan')
                    ->schema([
                Forms\Components\Select::make('status')
                            ->label('Status')
                    ->options([
                                EquipmentReport::STATUS_PENDING => 'Menunggu Persetujuan',
                                EquipmentReport::STATUS_APPROVED => 'Disetujui',
                                EquipmentReport::STATUS_REJECTED => 'Ditolak',
                    ])
                            ->default(EquipmentReport::STATUS_PENDING)
                            ->disabled(fn () => !$isAdmin && !$isSupervisor)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state === EquipmentReport::STATUS_REJECTED) {
                                    $set('rejection_reason', '');
                                }
                            }),
                            
                        Forms\Components\DateTimePicker::make('schedule_date')
                            ->label('Jadwalkan Maintenance')
                            ->default(now()->addDay())
                            ->visible(fn (Forms\Get $get) => $get('status') === EquipmentReport::STATUS_APPROVED && ($isAdmin || $isSupervisor)),
                            
                Forms\Components\Textarea::make('notes')
                            ->label('Catatan Untuk Maintenance')
                            ->visible(fn (Forms\Get $get) => $get('status') === EquipmentReport::STATUS_APPROVED && ($isAdmin || $isSupervisor)),
                            
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(fn (Forms\Get $get) => $get('status') === EquipmentReport::STATUS_REJECTED)
                            ->visible(fn (Forms\Get $get) => $get('status') === EquipmentReport::STATUS_REJECTED && ($isAdmin || $isSupervisor)),
                            
                        Forms\Components\Placeholder::make('reported_at_placeholder')
                            ->label('Dilaporkan Pada')
                            ->content(fn ($record) => $record && $record->reported_at ? $record->reported_at->format('d M Y H:i') : '-'),
                            
                        Forms\Components\Placeholder::make('reporter_placeholder')
                            ->label('Dilaporkan Oleh')
                            ->content(fn ($record) => $record && $record->reporter ? $record->reporter->name : '-'),
                            
                        Forms\Components\Placeholder::make('approved_at_placeholder')
                            ->label('Disetujui/Ditolak Pada')
                            ->content(fn ($record) => $record && $record->approved_at ? $record->approved_at->format('d M Y H:i') : '-')
                            ->visible(fn ($record) => $record && $record->status !== EquipmentReport::STATUS_PENDING),
                            
                        Forms\Components\Placeholder::make('approver_placeholder')
                            ->label('Disetujui/Ditolak Oleh')
                            ->content(fn ($record) => $record && $record->approver ? $record->approver->name : '-')
                            ->visible(fn ($record) => $record && $record->status !== EquipmentReport::STATUS_PENDING),
                            
                        Forms\Components\Placeholder::make('maintenance_id_placeholder')
                            ->label('ID Maintenance')
                            ->content(fn ($record) => $record && $record->maintenance_id ? $record->maintenance_id : '-')
                            ->visible(fn ($record) => $record && $record->status === EquipmentReport::STATUS_APPROVED),
                    ]),
                    
                Forms\Components\Hidden::make('reporter_id')
                    ->default(fn () => auth()->id()),
                    
                Forms\Components\Hidden::make('reported_at')
                    ->default(fn () => now()),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        $user = auth()->user();
        $isAdmin = $user && $user->role === 'admin';
        $isSupervisor = $user && $user->role === 'supervisor';
        
        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user) {
                // Operator hanya melihat laporan mereka sendiri
                if ($user->role === 'operator') {
                    $query->where('reporter_id', $user->id);
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('issue_description')
                    ->label('Deskripsi Kerusakan')
                    ->limit(50)
                    ->html(),
                    
                Tables\Columns\ImageColumn::make('issue_image')
                    ->label('Foto')
                    ->circular(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        EquipmentReport::STATUS_PENDING => 'Menunggu Persetujuan',
                        EquipmentReport::STATUS_APPROVED => 'Disetujui',
                        EquipmentReport::STATUS_REJECTED => 'Ditolak',
                        default => $state,
                    })
                    ->colors([
                        'warning' => EquipmentReport::STATUS_PENDING,
                        'success' => EquipmentReport::STATUS_APPROVED,
                        'danger' => EquipmentReport::STATUS_REJECTED,
                    ]),
                    
                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Prioritas')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        EquipmentReport::PRIORITY_LOW => 'Rendah',
                        EquipmentReport::PRIORITY_MEDIUM => 'Sedang',
                        EquipmentReport::PRIORITY_HIGH => 'Tinggi',
                        default => $state,
                    })
                    ->colors([
                        'success' => EquipmentReport::PRIORITY_LOW,
                        'warning' => EquipmentReport::PRIORITY_MEDIUM,
                        'danger' => EquipmentReport::PRIORITY_HIGH,
                    ]),
                    
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Pelapor')
                    ->description(fn ($record) => $record->issue_description && str_contains($record->issue_description, 'Dilaporkan oleh:') ? 
                        explode('Dilaporkan oleh:', $record->issue_description)[1] : '')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('reported_at')
                    ->label('Tanggal Laporan')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        EquipmentReport::STATUS_PENDING => 'Menunggu Persetujuan',
                        EquipmentReport::STATUS_APPROVED => 'Disetujui',
                        EquipmentReport::STATUS_REJECTED => 'Ditolak',
                    ]),
                    
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        EquipmentReport::PRIORITY_LOW => 'Rendah',
                        EquipmentReport::PRIORITY_MEDIUM => 'Sedang',
                        EquipmentReport::PRIORITY_HIGH => 'Tinggi',
                    ]),
                    
                Tables\Filters\SelectFilter::make('equipment_id')
                    ->relationship('equipment', 'name')
                    ->label('Equipment'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\EditAction::make(),
                
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\DateTimePicker::make('schedule_date')
                            ->label('Jadwalkan Maintenance')
                            ->default(now()->addDay())
                            ->required(),
                            
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Untuk Maintenance'),
                    ])
                    ->action(function (EquipmentReport $record, array $data) {
                        $maintenance = $record->approve(
                            auth()->id(),
                            $data['notes'] ?? null,
                            $data['schedule_date'] ?? now()->addDay()
                        );
                        
                        if ($maintenance) {
                            Notification::make()
                                ->title('Laporan kerusakan disetujui')
                                ->body('Jadwal maintenance berhasil dibuat.')
                                ->success()
                                ->send();
                        }
                    })
                    ->visible(fn (EquipmentReport $record) => 
                        ($isAdmin || $isSupervisor) && $record->status === EquipmentReport::STATUS_PENDING),
                    
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (EquipmentReport $record, array $data) {
                        $record->reject(auth()->id(), $data['rejection_reason']);
                        
                        Notification::make()
                            ->title('Laporan kerusakan ditolak')
                            ->body('Laporan telah ditandai sebagai ditolak.')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (EquipmentReport $record) => 
                        ($isAdmin || $isSupervisor) && $record->status === EquipmentReport::STATUS_PENDING),
                        
                Action::make('view_maintenance')
                    ->label('Lihat Maintenance')
                    ->icon('heroicon-o-wrench')
                    ->color('primary')
                    ->url(fn (EquipmentReport $record) => 
                        $record->maintenance_id ? 
                        MaintenanceResource::getUrl('view', ['record' => $record->maintenance_id]) : '#')
                    ->visible(fn (EquipmentReport $record) => 
                        $record->status === EquipmentReport::STATUS_APPROVED && $record->maintenance_id),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('approve_selected')
                    ->label('Setujui Terpilih')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($records) {
                        $successCount = 0;
                        
                        foreach ($records as $record) {
                            if ($record->status === EquipmentReport::STATUS_PENDING) {
                                $maintenance = $record->approve(auth()->id());
                                if ($maintenance) {
                                    $successCount++;
                                }
                            }
                        }
                        
                        Notification::make()
                            ->title("$successCount laporan berhasil disetujui")
                            ->body('Jadwal maintenance telah dibuat untuk semua laporan yang disetujui.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => $isAdmin || $isSupervisor)
                    ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListEquipmentReports::route('/'),
            'create' => Pages\CreateEquipmentReport::route('/create'),
            'edit' => Pages\EditEquipmentReport::route('/{record}/edit'),
            'view' => Pages\ViewEquipmentReport::route('/{record}'),
        ];
    }
} 