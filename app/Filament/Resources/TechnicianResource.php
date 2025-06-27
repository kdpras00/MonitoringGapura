<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class TechnicianResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Kelola Teknisi';
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'technician');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),

                Forms\Components\TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->maxLength(255)
                    ->dehydrated(fn($state) => !empty($state))
                    ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null),

                Forms\Components\Toggle::make('is_approved')
                    ->label('Disetujui')
                    ->default(true),

                Forms\Components\Hidden::make('role')
                    ->default('technician'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Status')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('approved')
                    ->label('Disetujui')
                    ->query(fn(Builder $query): Builder => $query->where('is_approved', true)),

                Tables\Filters\Filter::make('not_approved')
                    ->label('Belum Disetujui')
                    ->query(fn(Builder $query): Builder => $query->where('is_approved', false)),
            ])
            ->actions([
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
            'index' => \App\Filament\Resources\TechnicianResource\Pages\ListTechnicians::route('/'),
            'create' => \App\Filament\Resources\TechnicianResource\Pages\CreateTechnician::route('/create'),
            'edit' => \App\Filament\Resources\TechnicianResource\Pages\EditTechnician::route('/{record}/edit'),
        ];
    }
}
