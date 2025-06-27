<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalResource\Pages;
use App\Models\Maintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ApprovalResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationLabel = 'Approval Maintenance';
    protected static ?string $navigationGroup = 'Maintenance';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'supervisor';
    }

    public static function canCreate(): bool 
    {
        return false; // Maintenance tidak bisa dibuat dari panel approval
    }
    
    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'supervisor']);
    }
    
    public static function canDelete(Model $record): bool
    {
        return false; // Maintenance tidak bisa dihapus dari panel approval
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('approval_status', 'pending')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('approval_status', 'pending')->count() > 0
            ? 'warning'
            : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Approval')
                    ->schema([
                        Forms\Components\Select::make('approval_status')
                            ->label('Status Approval')
                            ->options([
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Catatan Approval')
                            ->rows(3)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('equipment.name')
                    ->label('Peralatan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('actual_date')
                    ->label('Tanggal Selesai')
                    ->dateTime('d M Y')
                    ->sortable(),

                TextColumn::make('technician_id')
                    ->label('Teknisi')
                    ->formatStateUsing(fn ($state, Maintenance $record) => 
                        $record->technician ? $record->technician->name : '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('result')
                    ->label('Hasil')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'good' => 'Baik',
                        'partial' => 'Sebagian',
                        'failed' => 'Gagal',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'good' => 'success',
                        'partial' => 'warning',
                        'failed' => 'danger',
                        default => 'secondary',
                    }),

                Tables\Columns\TextColumn::make('approval_status')
                    ->label('Status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'secondary',
                    }),
            ])
            ->defaultSort('actual_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Maintenance $record): bool => $record->approval_status === 'pending')
                    ->action(function (Maintenance $record): void {
                        $record->approval_status = 'approved';
                        $record->approval_notes = 'Disetujui oleh supervisor';
                        $record->approved_by = auth()->user()->name;
                        $record->approval_date = now();
                        $record->save();
                    }),
                    
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Maintenance $record): bool => $record->approval_status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('approval_notes')
                            ->label('Catatan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Maintenance $record, array $data): void {
                        $record->approval_status = 'rejected';
                        $record->approval_notes = $data['approval_notes'];
                        $record->approved_by = auth()->user()->name;
                        $record->approval_date = now();
                        $record->save();
                    }),
                    
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->modalHeading(fn (Maintenance $record) => "Detail Maintenance: " . 
                        ($record->equipment ? $record->equipment->name : $record->equipment_name ?? '-'))
                    ->modalContent(fn (Maintenance $record) => view('filament.resources.approval-resource.components.detail-modal', ['record' => $record])),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovals::route('/'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'completed')
            ->orderBy('actual_date', 'desc');
    }
} 