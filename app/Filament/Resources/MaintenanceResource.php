<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Filament\Resources\MaintenanceResource\RelationManagers;
use App\Models\Maintenance;
use App\Models\Equipment;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Manage Maintenance';

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canViewAny();
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
                    ->relationship('technician', 'name')
                    ->required(),
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
                        'planned' => 'Planned',
                        'in-progress' => 'In Progress',
                        'completed' => 'Completed',
                    ])
                    ->default('planned')
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
                        return $get('status') === 'completed';
                    }),
                Forms\Components\TextInput::make('duration')
                    ->label('Durasi (menit)')
                    ->numeric()
                    ->visible(function (Forms\Get $get) {
                        return $get('status') === 'completed';
                    }),
                Forms\Components\TextInput::make('location_lat')
                    ->label('Latitude')
                    ->hidden(function (Forms\Get $get) {
                        return $get('status') !== 'completed';
                    }),
                Forms\Components\TextInput::make('location_lng')
                    ->label('Longitude')
                    ->hidden(function (Forms\Get $get) {
                        return $get('status') !== 'completed';
                    }),
                Forms\Components\FileUpload::make('before_image')
                    ->label('Foto Sebelum Maintenance')
                    ->directory('maintenance-before')
                    ->required(),
                Forms\Components\FileUpload::make('after_image')
                    ->label('Foto Setelah Maintenance')
                    ->directory('maintenance-after')
                    ->visible(function (Forms\Get $get) {
                        return $get('status') === 'completed';
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
                        return $get('status') === 'completed';
                    }),
                Forms\Components\Textarea::make('approval_notes')
                    ->label('Catatan Approval')
                    ->visible(function (Forms\Get $get) {
                        return $get('status') === 'completed';
                    }),
                Forms\Components\DateTimePicker::make('next_service_date')
                    ->label('Jadwal Service Berikutnya')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('schedule_date')
                    ->label('Jadwal')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Teknisi'),
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
                        'warning' => 'planned',
                        'primary' => 'in-progress',
                        'success' => 'completed',
                    ]),
                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Approval')
                    ->colors([
                        'gray' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planned' => 'Planned',
                        'in-progress' => 'In Progress',
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
                Action::make('export')
                    ->label('Export Data')
                    ->icon('heroicon-o-archive-box')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function () {
                        $date = now()->format('Y-m');
                        $month = Carbon::parse($date)->month;

                        return Excel::download(new MaintenanceReportExport(now()->month, now()->year), 'maintenance-report.xlsx');
                    }),
                Action::make('export-pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-archive-box')
                    ->url(fn() => route('report.maintenance'))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TechnicianRelationManager::class,
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
        Notification::make()
            ->title('Reminder Maintenance')
            ->body('Anda memiliki jadwal maintenance untuk ' . $record->equipment->name)
            ->success()
            ->send();
    }
}
