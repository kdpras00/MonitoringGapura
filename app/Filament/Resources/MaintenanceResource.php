<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Filament\Resources\MaintenanceResource\RelationManagers;
use App\Models\Maintenance;
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
                    ->required(),
                Forms\Components\DateTimePicker::make('schedule_date')
                    ->label('Jadwal Maintenance')
                    ->required(),
                Forms\Components\DateTimePicker::make('actual_date')
                    ->label('Tanggal Aktual')
                    ->required(),
                Forms\Components\Select::make('technician_id')
                    ->label('Teknisi')
                    ->relationship('technician', 'name')
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
                FileUpload::make('attachments')
                    ->label('Lampiran')
                    ->directory('maintenance-attachments')
                    ->multiple()
                    ->required(),
                Forms\Components\DateTimePicker::make('next_service_date')
                    ->label('Jadwal Service Berikutnya')
                    ->required(),
                Forms\Components\Repeater::make('comments')
                    ->relationship()
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->required()
                    ])
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
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'planned',
                        'primary' => 'in-progress',
                        'success' => 'completed',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planned' => 'Planned',
                        'in-progress' => 'In Progress',
                        'completed' => 'Completed',
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
