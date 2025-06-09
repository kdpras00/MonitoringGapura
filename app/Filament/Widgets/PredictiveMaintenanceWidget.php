<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\PredictiveMaintenance;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class PredictiveMaintenanceWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Predictive Maintenance')
            ->description('Prediksi kapan equipment memerlukan maintenance berdasarkan riwayat')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('predictiveMaintenance.last_maintenance_date')
                    ->label('Maintenance Terakhir')
                    ->date('d M Y')
                    ->placeholder('Belum pernah'),

                Tables\Columns\TextColumn::make('predictiveMaintenance.next_maintenance_date')
                    ->label('Prediksi Berikutnya')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('predictiveMaintenance.condition_score')
                    ->label('Skor Kondisi')
                    ->numeric(0)
                    ->suffix('%')
                    ->sortable()
                    ->color(function ($record) {
                        if (!$record->predictiveMaintenance) return 'gray';
                        
                        $score = $record->predictiveMaintenance->condition_score;
                        return $score > 80 ? 'success' : ($score > 60 ? 'warning' : 'danger');
                    }),

                Tables\Columns\TextColumn::make('predictiveMaintenance.recommendation')
                    ->label('Rekomendasi')
                    ->wrap(),
            ])
            ->actions([
                Tables\Actions\Action::make('schedule')
                    ->label('Jadwalkan')
                    ->url(fn($record) => route('filament.admin.resources.maintenances.create', ['equipment_id' => $record->id]))
                    ->icon('heroicon-o-calendar')
                    ->color('primary'),

                Tables\Actions\Action::make('refresh')
                    ->label('Perbarui Prediksi')
                    ->action(function($record) {
                        // Perbarui prediksi untuk equipment ini
                        $this->refreshPrediction($record);
                    })
                    ->icon('heroicon-o-arrow-path')
                    ->color('success'),
            ])
            ->emptyStateHeading('Tidak ada data prediksi maintenance')
            ->emptyStateDescription('Jalankan command untuk mengisi data prediksi maintenance.')
            ->emptyStateIcon('heroicon-o-chart-bar')
            ->emptyStateActions([
                Tables\Actions\Action::make('run_command')
                    ->label('Jalankan Command')
                    ->url(url('/admin'))
                    ->icon('heroicon-o-command-line'),
            ])
            ->query(
                Equipment::query()
                    ->with('predictiveMaintenance')
            );
    }
    
    private function refreshPrediction($equipment)
    {
        // Get last maintenance
        $lastMaintenance = Maintenance::where('equipment_id', $equipment->id)
            ->where('status', 'completed')
            ->latest('actual_date')
            ->first();
            
        $lastMaintenanceDate = $lastMaintenance ? $lastMaintenance->actual_date : null;
        
        // Calculate next date (30-90 days from last maintenance or 10-30 days from now)
        $nextMaintenanceDate = $lastMaintenanceDate 
            ? Carbon::parse($lastMaintenanceDate)->addDays(rand(30, 90))->format('Y-m-d H:i:s')
            : Carbon::now()->addDays(rand(10, 30))->format('Y-m-d H:i:s');
            
        // Calculate condition score
        $conditionScore = $lastMaintenanceDate
            ? max(0, min(100, 100 - Carbon::parse($lastMaintenanceDate)->diffInDays(now()) / 2))
            : rand(50, 95);
            
        // Generate recommendation
        $recommendation = $conditionScore > 80 
            ? 'Equipment dalam kondisi baik. Pemeliharaan rutin direkomendasikan.' 
            : ($conditionScore > 60 
                ? 'Periksa equipment teratur. Jadwalkan inspeksi.' 
                : 'Maintenance segera diperlukan!');
                
        // Save or update predictive maintenance
        PredictiveMaintenance::updateOrCreate(
            ['equipment_id' => $equipment->id],
            [
                'last_maintenance_date' => $lastMaintenanceDate,
                'next_maintenance_date' => $nextMaintenanceDate,
                'condition_score' => $conditionScore,
                'recommendation' => $recommendation,
            ]
        );
    }
}
