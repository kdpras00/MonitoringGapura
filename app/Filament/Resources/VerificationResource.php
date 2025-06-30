<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VerificationResource\Pages;
use App\Models\Inspection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class VerificationResource extends Resource
{
    protected static ?string $model = Inspection::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Verification Inspection';
    protected static ?string $navigationGroup = 'Supervisor';
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'verifications';
    protected static ?string $breadcrumb = 'Verification Inspection';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'supervisor';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return self::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Inspection')
                    ->schema([
                        Forms\Components\TextInput::make('equipment.name')
                            ->label('Peralatan')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('inspection_date')
                            ->label('Tanggal Inspeksi')
                            ->disabled(),
                        Forms\Components\TextInput::make('technician.name')
                            ->label('Teknisi')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Belum Selesai',
                                'completed' => 'Selesai',
                                'verified' => 'Terverifikasi',
                                'rejected' => 'Ditolak',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Teknisi')
                            ->disabled()
                            ->rows(3),
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Catatan Verifikasi')
                            ->rows(3),
                    ]),
                Forms\Components\Section::make('Foto Inspeksi')
                    ->schema([
                        Forms\Components\FileUpload::make('before_image')
                            ->label('Foto Sebelum Inspeksi')
                            ->image()
                            ->disabled()
                            ->columnSpan(1),
                        Forms\Components\FileUpload::make('after_image')
                            ->label('Foto Setelah Inspeksi')
                            ->image()
                            ->disabled()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Hanya tampilkan inspection yang status completed
                $query->where('status', \App\Models\Inspection::STATUS_COMPLETED);
            })
            ->columns([
                Tables\Columns\TextColumn::make('equipment.name')
                    ->label('Peralatan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('inspection_date')
                    ->label('Tanggal Inspeksi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('before_image')
                    ->label('Foto Sebelum')
                    ->circular(),
                Tables\Columns\ImageColumn::make('after_image')
                    ->label('Foto Setelah')
                    ->circular(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Catatan Verifikasi'),
                    ])
                    ->action(function (Inspection $record, array $data) {
                        // Gunakan method safeVerify dari trait StatusSafety
                        $record->safeVerify($data['verification_notes'] ?? null, auth()->id());

                        // Update status maintenance menjadi completed (terverifikasi) jika ada
                        $maintenance = \App\Models\Maintenance::where('equipment_id', $record->equipment_id)
                            ->where('technician_id', $record->technician_id)
                            ->whereIn('status', ['in-progress', 'planned', 'pending'])
                            ->first();

                        if ($maintenance) {
                            $maintenance->status = 'completed'; // completed berarti sudah diverifikasi oleh supervisor
                            $maintenance->actual_date = now();
                            $maintenance->save();
                        }

                        Notification::make()
                            ->title('Inspection berhasil diverifikasi')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Inspection $record) => $record->status === 'completed'),
                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('verification_notes')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Inspection $record, array $data) {
                        // Gunakan method safeReject dari trait StatusSafety
                        $record->safeReject($data['verification_notes'], auth()->id());

                        Notification::make()
                            ->title('Inspection ditolak')
                            ->danger()
                            ->send();
                    })
                    ->visible(fn (Inspection $record) => $record->status === 'completed'),
            ])
            ->bulkActions([
                //
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
            'index' => Pages\ListVerifications::route('/'),
            'view' => Pages\ViewVerification::route('/{record}'),
        ];
    }
}
