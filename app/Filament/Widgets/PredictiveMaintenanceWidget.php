<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use App\Models\Maintenance;
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

                Tables\Columns\TextColumn::make('last_maintenance')
                    ->label('Maintenance Terakhir')
                    ->formatStateUsing(fn($record) => $this->getLastMaintenanceDate($record)),

                Tables\Columns\TextColumn::make('next_predicted')
                    ->label('Prediksi Maintenance Berikutnya')
                    ->formatStateUsing(fn($record) => $this->getPredictedMaintenanceDate($record)),

                Tables\Columns\TextColumn::make('condition_score')
                    ->label('Skor Kondisi')
                    ->formatStateUsing(fn($record) => $this->getConditionScore($record) . '%')
                    ->color(fn($record) => $this->getConditionColor($record)),

                Tables\Columns\TextColumn::make('recommendation')
                    ->label('Rekomendasi')
                    ->formatStateUsing(fn($record) => $this->getRecommendation($record))
                    ->wrap(),
            ])
            ->actions([
                Tables\Actions\Action::make('schedule')
                    ->label('Jadwalkan')
                    ->url(fn($record) => route('filament.admin.resources.maintenances.create', ['equipment_id' => $record->id]))
                    ->icon('heroicon-o-calendar')
                    ->color('primary'),
            ])
            ->query(Equipment::query());
    }

    private function getLastMaintenanceDate($equipment): string
    {
        $lastMaintenance = Maintenance::where('equipment_id', $equipment->id)
            ->where('status', 'completed')
            ->latest('actual_date')
            ->first();

        if (!$lastMaintenance) {
            return 'Belum pernah';
        }

        return Carbon::parse($lastMaintenance->actual_date)->format('d M Y');
    }

    private function getPredictedMaintenanceDate($equipment): string
    {
        $lastMaintenance = Maintenance::where('equipment_id', $equipment->id)
            ->where('status', 'completed')
            ->latest('actual_date')
            ->first();

        if (!$lastMaintenance) {
            return Carbon::parse($equipment->installation_date)->addDays(30)->format('d M Y');
        }

        // Hitung rata-rata interval maintenance dari 3 maintenance terakhir
        $lastMaintenances = Maintenance::where('equipment_id', $equipment->id)
            ->where('status', 'completed')
            ->latest('actual_date')
            ->take(3)
            ->get();

        if ($lastMaintenances->count() < 2) {
            // Jika hanya ada 1 maintenance, gunakan default 30 hari
            return Carbon::parse($lastMaintenance->actual_date)->addDays(30)->format('d M Y');
        }

        // Hitung rata-rata interval
        $intervals = [];
        $prevDate = null;

        foreach ($lastMaintenances as $maintenance) {
            $currentDate = Carbon::parse($maintenance->actual_date);

            if ($prevDate) {
                $intervals[] = $prevDate->diffInDays($currentDate);
            }

            $prevDate = $currentDate;
        }

        $avgInterval = count($intervals) > 0 ? array_sum($intervals) / count($intervals) : 30;

        return Carbon::parse($lastMaintenance->actual_date)->addDays($avgInterval)->format('d M Y');
    }

    private function getConditionScore($equipment): int
    {
        $lastMaintenance = Maintenance::where('equipment_id', $equipment->id)
            ->where('status', 'completed')
            ->latest('actual_date')
            ->first();

        if (!$lastMaintenance) {
            // Jika belum pernah maintenance, gunakan tanggal instalasi
            $daysSinceInstallation = Carbon::parse($equipment->installation_date)->diffInDays(Carbon::now());
            return max(0, 100 - ($daysSinceInstallation / 30) * 100);
        }

        $daysSinceLastMaintenance = Carbon::parse($lastMaintenance->actual_date)->diffInDays(Carbon::now());
        $predictedNextDate = Carbon::parse($this->getPredictedMaintenanceDate($equipment));
        $totalDaysInterval = Carbon::parse($lastMaintenance->actual_date)->diffInDays($predictedNextDate);

        if ($totalDaysInterval == 0) {
            return 0;
        }

        // Skor menurun secara linear dari 100% ke 0% mendekati tanggal prediksi
        $score = 100 - ($daysSinceLastMaintenance / $totalDaysInterval) * 100;
        return max(0, min(100, (int)$score));
    }

    private function getConditionColor($equipment): string
    {
        $score = $this->getConditionScore($equipment);

        if ($score >= 70) {
            return 'success';
        } elseif ($score >= 40) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    private function getRecommendation($equipment): string
    {
        $score = $this->getConditionScore($equipment);
        $predictedDate = Carbon::parse($this->getPredictedMaintenanceDate($equipment));
        $daysUntilMaintenance = Carbon::now()->diffInDays($predictedDate, false);

        if ($score < 30) {
            return 'Segera lakukan maintenance! Equipment sudah dalam kondisi kritis.';
        } elseif ($score < 50) {
            return "Jadwalkan maintenance dalam {$daysUntilMaintenance} hari. Kondisi equipment memburuk.";
        } elseif ($score < 70) {
            return "Persiapkan maintenance dalam {$daysUntilMaintenance} hari. Pantau kondisi equipment.";
        } else {
            return "Equipment dalam kondisi baik. Maintenance berikutnya dalam {$daysUntilMaintenance} hari.";
        }
    }
}
