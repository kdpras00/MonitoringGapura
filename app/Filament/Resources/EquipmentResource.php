<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentResource\Pages;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Manage Equipment';

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->role === 'admin' || $user->role === 'technician');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nama Equipment'),

                Forms\Components\TextInput::make('serial_number')
                    ->required()
                    ->unique(ignoreRecord: true) // Mencegah error saat edit
                    ->label('Nomor Seri'),

                Forms\Components\TextInput::make('location')
                    ->required()
                    ->label('Lokasi'),

                Forms\Components\DatePicker::make('installation_date')
                    ->required()
                    ->label('Tanggal Instalasi'),

                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'maintenance' => 'Under Maintenance',
                        'retired' => 'Retired',
                    ])
                    ->default('active')
                    ->label('Status'),

                FileUpload::make('manual_url')
                    ->label('Manual Book')
                    ->directory('manuals')
                    ->acceptedFileTypes(['application/pdf']),

                Forms\Components\Textarea::make('specifications')
                    ->label('Spesifikasi Teknis')
                    ->required()
                    ->rules(['string', 'max:1000']),

                FileUpload::make('sop_url')
                    ->label('SOP Document')
                    ->directory('sops')
                    ->acceptedFileTypes(['application/pdf']),

                Forms\Components\Repeater::make('checklist')
                    ->label('Maintenance Checklist')
                    ->schema([
                        Forms\Components\TextInput::make('step')
                            ->required()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Nomor Seri'),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi'),

                Tables\Columns\ImageColumn::make('qr_code')
                    ->label('QR Code')
                    ->getStateUsing(fn($record) => $record->generateQrCode()),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'maintenance',
                        'danger' => 'retired',
                    ]),

                Tables\Columns\TextColumn::make('checklist')
                    ->label('Checklist Steps')
                    ->formatStateUsing(fn($state) => is_array($decoded = json_decode($state, true)) ? count($decoded) : 0)

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'maintenance' => 'Under Maintenance',
                        'retired' => 'Retired',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
            'view' => Pages\ViewEquipment::route('/{record}'), // Pastikan file ini ada
        ];
    }
}
