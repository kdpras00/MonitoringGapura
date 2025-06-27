<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupervisorRoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SupervisorRoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'Pengaturan';
    
    protected static ?string $navigationLabel = 'Kelola Supervisor';
    
    protected static ?string $modelLabel = 'Role Supervisor';
    
    protected static ?string $pluralModelLabel = 'Role Supervisor';
    
    // Menonaktifkan navigasi untuk resource ini
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Role')
                        ->required()
                        ->maxLength(255)
                        ->disabled(fn ($record) => $record && $record->name === 'supervisor'),
                    
                    Forms\Components\CheckboxList::make('permissions')
                        ->label('Hak Akses')
                        ->relationship('permissions', 'name')
                        ->columns(2),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Role')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TagsColumn::make('permissions.name')
                    ->label('Hak Akses'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        // Hanya tampilkan role supervisor
        return parent::getEloquentQuery()->where('name', 'supervisor');
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupervisorRoles::route('/'),
            'create' => Pages\CreateSupervisorRole::route('/create'),
            'edit' => Pages\EditSupervisorRole::route('/{record}/edit'),
        ];
    }
    
    public static function canViewAny(): bool
    {
        // Hanya admin yang dapat mengelola role supervisor
        return auth()->user()->hasRole('admin');
    }
}
