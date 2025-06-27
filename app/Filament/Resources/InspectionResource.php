<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InspectionResource\Pages;
use App\Models\Inspection;
use App\Models\Equipment;
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

class InspectionResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Kelola Inspection';
    protected static ?string $navigationGroup = 'Teknisi';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && $user->isTechnician();
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
                    ->label('Peralatan')
                    ->options(Equipment::all()->pluck('name', 'equipment_id'))
                    ->searchable()
                    ->required(),
                Forms\Components\DateTimePicker::make('inspection_date')
                    ->label('Tanggal Inspeksi')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Belum Selesai',
                        'completed' => 'Selesai',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(3),
                Forms\Components\FileUpload::make('before_image')
                    ->label('Foto Sebelum Inspeksi')
                    ->image()
                    ->directory('inspections/before')
                    ->visibility('public'),
                Forms\Components\FileUpload::make('after_image')
                    ->label('Foto Setelah Inspeksi')
                    ->image()
                    ->directory('inspections/after')
                    ->visibility('public')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'completed'),
                Forms\Components\CheckboxList::make('checklist')
                    ->label('Checklist Inspeksi')
                    ->options([
                        'inspeksi_visual' => 'Inspeksi Visual',
                        'uji_fungsi' => 'Uji Fungsi',
                        'pembersihan' => 'Pembersihan',
                        'pengecekan_komponen' => 'Pengecekan Komponen',
                        'pengujian_keamanan' => 'Pengujian Keamanan',
                    ])
                    ->columns(2),
                Forms\Components\TextInput::make('location')
                    ->label('Lokasi')
                    ->maxLength(255),
                Forms\Components\Hidden::make('technician_id')
                    ->default(fn () => Auth::id()),
                Forms\Components\Hidden::make('location_lat'),
                Forms\Components\Hidden::make('location_lng'),
                Forms\Components\Hidden::make('location_timestamp'),
                Forms\Components\DateTimePicker::make('completion_date')
                    ->label('Tanggal Penyelesaian')
                    ->visible(fn (Forms\Get $get) => $get('status') === 'completed')
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Peralatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('inspection_date')
                    ->label('Tanggal Inspeksi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                    ]),
                Tables\Columns\TextColumn::make('completion_date')
                    ->label('Tanggal Penyelesaian')
                    ->dateTime('d M Y H:i')
                    ->sortable()
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
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Belum Selesai',
                        'completed' => 'Selesai',
                    ]),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInspections::route('/'),
            'create' => Pages\CreateInspection::route('/create'),
            'edit' => Pages\EditInspection::route('/{record}/edit'),
            'view' => Pages\ViewInspection::route('/{record}'),
        ];
    }
} 