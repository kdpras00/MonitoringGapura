<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SparePartResource\Pages;
use App\Filament\Resources\SparePartResource\RelationManagers;
use App\Models\SparePart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SparePartResource extends Resource
{
    protected static ?string $model = SparePart::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Kelola Sparepart';
    protected static ?string $navigationGroup = 'Administrator';

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
                Forms\Components\TextInput::make('name')
                    ->label('Nama Spare Part')
                    ->required(),

                Forms\Components\TextInput::make('part_number')
                    ->label('Nomor Part')
                    ->required(),
                    
                Forms\Components\TextInput::make('barcode')
                    ->label('Kode Barcode')
                    ->helperText('Dibuat otomatis jika dikosongkan')
                    ->placeholder('SP00000000'),

                Forms\Components\TextInput::make('stock')
                    ->label('Jumlah')
                    ->numeric()
                    ->required(),
                    
                Forms\Components\TextInput::make('min_stock')
                    ->label('Jumlah Minimum')
                    ->helperText('Stok akan dianggap rendah jika di bawah nilai ini')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('Harga')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'low_stock' => 'Stok Rendah',
                        'out_of_stock' => 'Habis',
                    ])
                    ->default('available')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                Tables\Columns\TextColumn::make('part_number')
                    ->label('Nomor Part')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('barcode')
                    ->label('Kode Barcode')
                    ->searchable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Jumlah')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'available' => 'Tersedia',
                        'low_stock' => 'Stok Rendah',
                        'out_of_stock' => 'Habis',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'available' => 'success',
                        'low_stock' => 'warning',
                        'out_of_stock' => 'danger',
                        default => 'secondary',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'available' => 'Tersedia',
                        'low_stock' => 'Stok Rendah',
                        'out_of_stock' => 'Habis',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('print_barcode')
                    ->label('Cetak Barcode')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->url(fn (SparePart $record) => route('spare-parts.barcode', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSpareParts::route('/'),
            'create' => Pages\CreateSparePart::route('/create'),
            'edit' => Pages\EditSparePart::route('/{record}/edit'),
        ];
    }
}
