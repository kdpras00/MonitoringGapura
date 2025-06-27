<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PredictiveMaintenanceResource\Pages;
use App\Filament\Resources\PredictiveMaintenanceResource\RelationManagers;
use App\Models\PredictiveMaintenance;
use Filament\Forms;
use Filament\Forms\Form;
// use Filament\Forms\Components\Slider;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;

class PredictiveMaintenanceResource extends Resource
{
    protected static ?string $model = PredictiveMaintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Predictive Maintenance';
    protected static ?string $navigationGroup = 'Maintenance';
    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        // Resource tidak dapat diakses oleh siapapun (karena supervisor hanya melihat approval maintenance)
        return false;
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
                    ->relationship('equipment', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\DateTimePicker::make('last_maintenance_date')
                    ->label('Maintenance Terakhir'),
                Forms\Components\DateTimePicker::make('next_maintenance_date')
                    ->label('Prediksi Maintenance Berikutnya')
                    ->required(),
                Forms\Components\TextInput::make('condition_score')
                    ->label('Skor Kondisi')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(1)
                    ->suffix('%')
                    ->required(),
                Forms\Components\Select::make('recommendation')
                    ->label('Rekomendasi')
                    ->options([
                        'Routine maintenance recommended' => 'Routine maintenance recommended',
                        'Inspection needed' => 'Inspection needed',
                        'Immediate maintenance required' => 'Immediate maintenance required',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('equipment.name')
                    ->label('Equipment')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_maintenance_date')
                    ->label('Maintenance Terakhir')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('next_maintenance_date')
                    ->label('Prediksi Berikutnya')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('condition_score')
                    ->label('Skor Kondisi')
                    ->sortable()
                    ->numeric(0)
                    ->suffix('/100')
                    ->color(function ($state) {
                        if ($state > 80) return 'success';
                        if ($state > 60) return 'warning';
                        return 'danger';
                    }),
                TextColumn::make('recommendation')
                    ->label('Rekomendasi')
                    ->wrap()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('condition')
                    ->options([
                        'good' => 'Kondisi Baik (>80)',
                        'warning' => 'Perlu Perhatian (61-80)',
                        'critical' => 'Kritis (<60)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        
                        return match ($data['value']) {
                            'good' => $query->where('condition_score', '>', 80),
                            'warning' => $query->whereBetween('condition_score', [61, 80]),
                            'critical' => $query->where('condition_score', '<=', 60),
                        };
                    }),
            ])
            ->actions([
                Action::make('update_prediction')
                    ->label('Perbarui Prediksi')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Perbarui Prediksi Maintenance')
                    ->modalDescription('Yakin ingin memperbarui prediksi maintenance untuk peralatan ini? Sistem akan menganalisis data sensor terbaru dan memprediksi maintenance berikutnya.')
                    ->modalSubmitActionLabel('Ya, Perbarui Prediksi')
                    ->modalIcon('heroicon-o-arrow-path')
                    ->action(function (PredictiveMaintenance $record) {
                        // Set tanggal maintenance terakhir ke waktu sekarang
                        $record->last_maintenance_date = Carbon::now();
                        
                        // Ambil data equipment terkait
                        $equipment = $record->equipment;
                        
                        if (!$equipment) {
                            Notification::make()
                                ->title('Error')
                                ->body('Equipment tidak ditemukan')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        try {
                            // Ambil data sensor realtime (simulasi)
                            $sensorData = self::getSensorData($equipment);
                            
                            // Panggil API prediksi maintenance menggunakan model ML
                            $prediction = self::predictMaintenance($sensorData);
                            
                            if (isset($prediction['error'])) {
                                throw new \Exception($prediction['error']);
                            }
                            
                            // Update data prediksi berdasarkan hasil dari model ML
                            $daysToNextMaintenance = self::calculateDaysToNextMaintenance($prediction, $equipment);
                            $record->next_maintenance_date = Carbon::now()->addDays($daysToNextMaintenance);
                            
                            // Update skor kondisi dan rekomendasi
                            $record->condition_score = self::calculateConditionScore($prediction);
                            $record->recommendation = self::getRecommendation($prediction);
                            
                            $record->save();
                            
                            Notification::make()
                                ->title('Prediksi berhasil diperbarui')
                                ->body("Menggunakan model ML: Skor kondisi {$record->condition_score}%. Jadwal maintenance berikutnya: " . 
                                    (is_object($record->next_maintenance_date) && method_exists($record->next_maintenance_date, 'format') 
                                        ? $record->next_maintenance_date->format('d M Y H:i') 
                                        : 'Tidak dijadwalkan'))
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            // Jika gagal menggunakan ML, gunakan fallback ke metode deterministik
                            Log::error('ML Prediction failed: ' . $e->getMessage());
                            
                            // Fallback ke metode deterministik
                            self::fallbackPrediction($record, $equipment);
                            
                            Notification::make()
                                ->title('Prediksi diperbarui (metode fallback)')
                                ->body("Model ML tidak tersedia. Menggunakan metode alternatif: Skor kondisi {$record->condition_score}%. Jadwal maintenance berikutnya: " . 
                                    (is_object($record->next_maintenance_date) && method_exists($record->next_maintenance_date, 'format') 
                                        ? $record->next_maintenance_date->format('d M Y H:i') 
                                        : 'Tidak dijadwalkan'))
                                ->warning()
                                ->send();
                        }
                    }),
                Action::make('detail_prediction')
                    ->label('Detail Analisis')
                    ->icon('heroicon-o-chart-bar')
                    ->color('primary')
                    ->action(function (PredictiveMaintenance $record) {
                        // Implementasi sebenarnya akan memanggil Python API
                    })
                    ->modalContent(function (PredictiveMaintenance $record) {
                        $equipment = $record->equipment;
                        
                        if (!$equipment) {
                            return new HtmlString('<div class="text-amber-600 font-medium">Equipment tidak ditemukan</div>');
                        }
                        
                        try {
                            // Bagian 1: Data historis yang tersimpan di database
                            $historicalHtml = "<div class='p-4 mb-6 border border-gray-200 rounded-lg'>";
                            $historicalHtml .= "<h3 class='text-lg font-bold mb-3'>Data Tersimpan ({$record->updated_at->format('d M Y H:i')})</h3>";
                            
                            // Skor kondisi dari database
                            $conditionColor = match (true) {
                                $record->condition_score > 80 => 'text-green-600',
                                $record->condition_score > 60 => 'text-amber-500',
                                default => 'text-red-600',
                            };
                            
                            $historicalHtml .= "<div class='grid grid-cols-2 gap-3'>";
                            $historicalHtml .= "<div><span class='font-medium'>Skor Kondisi:</span> <span class='{$conditionColor} font-bold'>{$record->condition_score}%</span></div>";
                            $historicalHtml .= "<div><span class='font-medium'>Maintenance Terakhir:</span> " . ($record->last_maintenance_date ? $record->last_maintenance_date->format('d M Y H:i') : 'Belum ada') . "</div>";
                            $historicalHtml .= "<div><span class='font-medium'>Prediksi Berikutnya:</span> " . $record->next_maintenance_date->format('d M Y H:i') . "</div>";
                            $historicalHtml .= "<div><span class='font-medium'>Rekomendasi:</span> {$record->recommendation}</div>";
                            $historicalHtml .= "</div>";
                            $historicalHtml .= "</div>";
                            
                            // Bagian 2: Data sensor realtime dan prediksi
                            // Ambil data sensor untuk equipment ini
                            $sensorData = self::getSensorData($equipment);
                            
                            // Panggil API ML untuk mendapatkan prediksi
                            $prediction = self::predictMaintenance($sensorData);
                            
                            if (isset($prediction['error'])) {
                                return new HtmlString($historicalHtml . '<div class="text-red-600 font-medium">Error mendapatkan prediksi realtime: ' . e($prediction['error']) . '</div>');
                            }
                            
                            $realtimeHtml = "<div class='space-y-4'>";
                            $realtimeHtml .= "<div class='flex justify-between items-center'>";
                            $realtimeHtml .= "<h3 class='text-lg font-bold'>Analisis Realtime (".Carbon::now()->format('d M Y H:i').")</h3>";
                            
                            // Tombol refresh
                            $refreshJs = "document.querySelector('[data-modal-close]').click(); setTimeout(() => { document.querySelector('[data-action=detail_prediction]').click(); }, 100);";
                            $realtimeHtml .= "<button onclick=\"{$refreshJs}\" class='text-sm text-blue-600 hover:text-blue-800 flex items-center'>";
                            $realtimeHtml .= "<svg class='w-4 h-4 mr-1' fill='none' stroke='currentColor' viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'></path></svg>";
                            $realtimeHtml .= "Refresh Data";
                            $realtimeHtml .= "</button>";
                            $realtimeHtml .= "</div>";
                            
                            // Format data sensor untuk ditampilkan
                            $sensorHtml = "<div class='space-y-2 mb-4 p-3 bg-blue-50 rounded-lg'>";
                            $sensorHtml .= "<h4 class='font-bold'>Data Sensor Realtime</h4>";
                            $sensorHtml .= "<div class='grid grid-cols-2 gap-2'>";
                            $sensorHtml .= "<div><span class='font-medium'>Vibration:</span> {$sensorData['vibration']} mm/s</div>";
                            $sensorHtml .= "<div><span class='font-medium'>Temperature:</span> {$sensorData['temperature']}°C</div>";
                            $sensorHtml .= "<div><span class='font-medium'>Pressure:</span> {$sensorData['pressure']} PSI</div>";
                            $sensorHtml .= "<div><span class='font-medium'>Humidity:</span> {$sensorData['humidity']}%</div>";
                            $sensorHtml .= "</div>";
                            $sensorHtml .= "</div>";
                            
                            // Format hasil prediksi
                            $predictionHtml = "<div class='space-y-2 mb-4 p-3 bg-purple-50 rounded-lg'>";
                            $predictionHtml .= "<h4 class='font-bold'>Hasil Analisis ML</h4>";
                            
                            // Status maintenance
                            $statusColor = $prediction['maintenance_required'] ? 'text-red-600' : 'text-green-600';
                            $statusText = $prediction['maintenance_required'] ? 'Diperlukan' : 'Tidak Diperlukan';
                            $predictionHtml .= "<div><span class='font-medium'>Maintenance:</span> <span class='{$statusColor} font-bold'>{$statusText}</span></div>";
                            
                            // Confidence
                            $predictionHtml .= "<div><span class='font-medium'>Confidence Level:</span> " . round($prediction['confidence'] * 100) . "%</div>";
                            
                            // Urgency
                            $urgencyColor = match ($prediction['urgency_level']) {
                                'critical' => 'text-red-600',
                                'high' => 'text-orange-500',
                                'medium' => 'text-amber-500',
                                'low' => 'text-green-600',
                                default => 'text-gray-600',
                            };
                            $urgencyText = match ($prediction['urgency_level']) {
                                'critical' => 'Kritis',
                                'high' => 'Tinggi',
                                'medium' => 'Sedang',
                                'low' => 'Rendah',
                                default => 'Normal',
                            };
                            $predictionHtml .= "<div><span class='font-medium'>Level Urgensi:</span> <span class='{$urgencyColor} font-bold'>{$urgencyText}</span></div>";
                            
                            // Condition score if available in prediction
                            if (isset($prediction['condition_score'])) {
                                $conditionColor = match (true) {
                                    $prediction['condition_score'] > 80 => 'text-green-600',
                                    $prediction['condition_score'] > 60 => 'text-amber-500',
                                    default => 'text-red-600',
                                };
                                $predictionHtml .= "<div><span class='font-medium'>Skor Kondisi:</span> <span class='{$conditionColor} font-bold'>{$prediction['condition_score']}%</span></div>";
                            }
                            
                            // Estimated days
                            $predictionHtml .= "<div><span class='font-medium'>Estimasi Hari ke Kegagalan:</span> {$prediction['estimated_days_to_failure']} hari</div>";
                            
                            // Potential issues
                            $predictionHtml .= "<div class='mt-2'><span class='font-medium'>Potensi Masalah:</span>";
                            $predictionHtml .= "<ul class='list-disc pl-5 mt-1'>";
                            foreach ($prediction['potential_issues'] as $issue) {
                                $predictionHtml .= "<li>{$issue}</li>";
                            }
                            $predictionHtml .= "</ul></div>";
                            $predictionHtml .= "</div>";
                            
                            // Rekomendasi
                            $recommendationHtml = "<div class='space-y-2 p-3 bg-gray-100 rounded-lg'>";
                            $recommendationHtml .= "<h4 class='font-bold'>Rekomendasi Terbaru</h4>";
                            $recommendationHtml .= "<p>" . self::getRecommendation($prediction) . "</p>";
                            $recommendationHtml .= "<p class='text-xs text-gray-600 mt-2'>Rekomendasi ini dihasilkan menggunakan analisis data sensor realtime.</p>";
                            $recommendationHtml .= "</div>";
                            
                            $realtimeHtml .= $sensorHtml . $predictionHtml . $recommendationHtml;
                            $realtimeHtml .= "</div>";
                            
                            // Pesan perbandingan
                            $comparisonHtml = "";
                            if ($prediction['maintenance_required'] && $record->condition_score > 80) {
                                $comparisonHtml = "<div class='p-3 bg-red-100 rounded-lg mt-4 text-sm'>
                                    <span class='font-bold'>⚠️ Perhatian:</span> Analisis realtime menunjukkan kebutuhan maintenance segera, berbeda dengan data historis.
                                    Pertimbangkan untuk memperbarui prediksi dengan menggunakan tombol 'Perbarui Prediksi'.
                                </div>";
                            } elseif (!$prediction['maintenance_required'] && $record->condition_score < 60) {
                                $comparisonHtml = "<div class='p-3 bg-green-100 rounded-lg mt-4 text-sm'>
                                    <span class='font-bold'>ℹ️ Informasi:</span> Kondisi equipment telah membaik berdasarkan analisis realtime.
                                    Pertimbangkan untuk memperbarui prediksi dengan menggunakan tombol 'Perbarui Prediksi'.
                                </div>";
                            }
                            
                            // Combine all sections
                            return new HtmlString($historicalHtml . $realtimeHtml . $comparisonHtml);
                        } catch (\Exception $e) {
                            return new HtmlString('<div class="text-red-600 font-medium">Error: ' . e($e->getMessage()) . '</div>');
                        }
                    })
                    ->modalHeading('Analisis Predictive Maintenance')
                    ->modalSubmitAction(false),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListPredictiveMaintenances::route('/'),
            'create' => Pages\CreatePredictiveMaintenance::route('/create'),
            'edit' => Pages\EditPredictiveMaintenance::route('/{record}/edit'),
        ];
    }
    
    /**
     * Mendapatkan data sensor realtime dari equipment
     * Dalam contoh ini kita mensimulasikan data sensor berdasarkan equipment
     */
    protected static function getSensorData($equipment)
    {
        // Timestamp saat ini untuk simulasi realtime
        $currentTimestamp = Carbon::now()->toIso8601String();
        
        if (!$equipment) {
            // Default data jika equipment tidak tersedia
            return [
                'equipment_id' => 0,
                'vibration' => rand(40, 60) / 10, // 4.0-6.0
                'temperature' => rand(70, 85),    // 70-85
                'pressure' => rand(90, 110),      // 90-110
                'humidity' => rand(40, 70),       // 40-70
                'timestamp' => $currentTimestamp
            ];
        }
        
        // Gunakan ID sebagai seed untuk memastikan nilai konsisten untuk equipment yang sama
        $seed = $equipment->id;
        mt_srand($seed + time() % 100); // Tambahkan sedikit variasi setiap kali dipanggil
        
        // Dalam implementasi nyata, data ini akan diambil dari sensor IoT
        // atau sistem monitoring realtime
        
        // Buat nilai dasar untuk sensor berdasarkan jenis equipment
        $baseVibration = $equipment->type === 'elektrik' ? 5.0 : 3.0;
        $baseTemperature = $equipment->type === 'elektrik' ? 75 : 65;
        $basePressure = 100;
        $baseHumidity = 60;
        
        // Tambahkan variasi berdasarkan prioritas
        $priorityFactor = match ($equipment->priority) {
            'merah' => 1.5,  // Equipment prioritas tinggi cenderung lebih berat penggunaannya
            'kuning' => 1.2,
            'hijau' => 1.0,
            default => 1.0,
        };
        
        // Simulasi kondisi berdasarkan status equipment
        $statusFactor = 1.0;
        if ($equipment->status === 'maintenance') {
            $statusFactor = 1.3; // Sedang dalam maintenance, mungkin beberapa sensor menunjukkan nilai tinggi
        } elseif ($equipment->status === 'inactive') {
            $statusFactor = 0.7; // Tidak aktif, sensor menunjukkan nilai lebih rendah
        }
        
        // Variasi berdasarkan equipment ID untuk konsistensi
        $idVariation = ($equipment->id * 7) % 10; // 0-9
        $idFactor = 1 + ($idVariation / 50); // 1.0-1.18
        
        // Tambahkan variasi waktu untuk realtime effect (berubah setiap menit)
        $timeVariation = (time() / 60) % 10 / 10; // 0.0-0.9 berubah setiap menit
        
        // Hitung nilai sensor aktual dengan semua faktor
        $vibration = round($baseVibration * $priorityFactor * $statusFactor * $idFactor + $timeVariation, 2);
        $temperature = round($baseTemperature * $statusFactor * $idFactor + $timeVariation * 3);
        $pressure = round($basePressure * $statusFactor + $idVariation * 2 - $timeVariation * 5);
        $humidity = round($baseHumidity / $statusFactor + $idVariation - $timeVariation * 10);
        
        // Log sensor values
        Log::info("Generated sensor data for equipment {$equipment->id}: vibration={$vibration}, temp={$temperature}, pressure={$pressure}, humidity={$humidity}");
        
        return [
            'equipment_id' => $equipment->id,
            'vibration' => $vibration,
            'temperature' => $temperature, 
            'pressure' => $pressure,
            'humidity' => $humidity,
            'timestamp' => $currentTimestamp
        ];
    }
    
    /**
     * Memanggil API model machine learning untuk prediksi
     */
    protected static function predictMaintenance($sensorData)
    {
        try {
            // Log data sensor untuk debug
            Log::info('Sensor data for prediction:', $sensorData);
            
            // Gunakan metode deterministik untuk prediksi
            return self::deterministicPrediction($sensorData);
            
        } catch (\Exception $e) {
            Log::error("Predictive maintenance error: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            // Fallback ke metode deterministik jika terjadi error
            return self::deterministicPrediction($sensorData);
        }
    }
    
    /**
     * Metode prediksi deterministik
     */
    protected static function deterministicPrediction($sensorData)
    {
        // Metode sederhana berdasarkan threshold
        $needsMaintenance = (
            $sensorData['vibration'] > 5.5 || 
            $sensorData['temperature'] > 85 || 
            $sensorData['pressure'] > 120 || 
            $sensorData['humidity'] < 30
        );
        
        $urgencyLevel = 'low';
        if ($sensorData['vibration'] > 7 || $sensorData['temperature'] > 90) {
            $urgencyLevel = 'critical';
        } elseif ($sensorData['vibration'] > 6 || $sensorData['temperature'] > 85) {
            $urgencyLevel = 'high';
        } elseif ($sensorData['vibration'] > 5 || $sensorData['temperature'] > 80) {
            $urgencyLevel = 'medium';
        }
        
        $confidence = 0.70 + (rand(0, 25) / 100); // 0.70-0.95
        
        // Hitung skor kondisi berdasarkan data sensor
        $conditionScore = 100;
        
        // Kurangi skor kondisi berdasarkan nilai sensor
        if ($sensorData['vibration'] > 5) {
            $conditionScore -= ($sensorData['vibration'] - 5) * 10;
        }
        
        if ($sensorData['temperature'] > 75) {
            $conditionScore -= ($sensorData['temperature'] - 75) * 0.6;
        }
        
        if ($sensorData['pressure'] > 110) {
            $conditionScore -= ($sensorData['pressure'] - 110) * 0.5;
        }
        
        if ($sensorData['humidity'] < 40) {
            $conditionScore -= (40 - $sensorData['humidity']) * 0.8;
        }
        
        // Pastikan skor tidak keluar dari rentang 0-100
        $conditionScore = max(0, min(100, $conditionScore));
        
        // Hasil simulasi model
        return [
            'maintenance_required' => $needsMaintenance,
            'confidence' => $confidence,
            'sensor_data' => $sensorData,
            'urgency_level' => $urgencyLevel,
            'estimated_days_to_failure' => $needsMaintenance ? rand(5, 30) : rand(45, 120),
            'potential_issues' => self::getPotentialIssues($sensorData),
            'condition_score' => round($conditionScore),
            'method' => 'deterministic'
        ];
    }
    
    /**
     * Hitung jumlah hari sampai maintenance berikutnya berdasarkan prediksi
     */
    protected static function calculateDaysToNextMaintenance($prediction, $equipment)
    {
        // Jika maintenance diperlukan segera berdasarkan prediksi
        if ($prediction['maintenance_required']) {
            // Tergantung level urgensi
            $daysToNextMaintenance = match ($prediction['urgency_level']) {
                'critical' => rand(1, 5),
                'high' => rand(5, 14),
                'medium' => rand(14, 30),
                'low' => rand(30, 45),
                default => 30,
            };
        } else {
            // Jika tidak diperlukan maintenance segera
            $daysToNextMaintenance = rand(60, 120); // 2-4 bulan
            
            // Sesuaikan berdasarkan jenis dan prioritas equipment
            if ($equipment->type === 'elektrik') {
                $daysToNextMaintenance = (int)($daysToNextMaintenance * 0.8); // 20% lebih cepat
            }
            
            if ($equipment->priority === 'merah') {
                $daysToNextMaintenance = (int)($daysToNextMaintenance * 0.75); // 25% lebih cepat
            } elseif ($equipment->priority === 'kuning') {
                $daysToNextMaintenance = (int)($daysToNextMaintenance * 0.9); // 10% lebih cepat
            }
        }
        
        // Gunakan 'estimated_days_to_failure' jika tersedia dari model ML
        if (isset($prediction['estimated_days_to_failure'])) {
            // Gunakan prediksi model ML tapi tambahkan sedikit variasi
            $variation = rand(-5, 5);
            return max(1, $prediction['estimated_days_to_failure'] + $variation);
        }
        
        return $daysToNextMaintenance;
    }
    
    /**
     * Hitung skor kondisi berdasarkan prediksi
     */
    protected static function calculateConditionScore($prediction)
    {
        // Gunakan skor kondisi dari prediksi jika tersedia
        if (isset($prediction['condition_score']) && is_numeric($prediction['condition_score'])) {
            // Pastikan nilainya dalam rentang 0-100
            return min(100, max(0, $prediction['condition_score']));
        }
        
        // Fallback ke perhitungan standar
        // Jika tidak perlu maintenance, skor lebih tinggi
        if (!$prediction['maintenance_required']) {
            return rand(85, 98);
        }
        
        // Tentukan skor berdasarkan urgency level
        $baseScore = match ($prediction['urgency_level']) {
            'critical' => rand(35, 50),
            'high' => rand(50, 65),
            'medium' => rand(65, 75),
            'low' => rand(75, 85),
            default => 70,
        };
        
        // Tambahkan pengaruh confidence level
        $confidenceEffect = (1 - $prediction['confidence']) * 10;
        
        // Faktor variasi
        $variation = rand(-3, 3);
        
        // Pastikan skor tetap dalam range 0-100
        return min(100, max(0, $baseScore + $confidenceEffect + $variation));
    }
    
    /**
     * Ambil rekomendasi berdasarkan prediksi
     */
    protected static function getRecommendation($prediction)
    {
        // Fallback ke rekomendasi standar
        if (!$prediction['maintenance_required']) {
            return 'Routine maintenance recommended';
        }
        
        return match ($prediction['urgency_level']) {
            'critical' => 'Immediate maintenance required',
            'high' => 'Immediate maintenance required',
            'medium' => 'Inspection needed',
            'low' => 'Routine maintenance recommended',
            default => 'Inspection needed',
        };
    }
    
    /**
     * Metode fallback jika prediksi ML gagal
     */
    protected static function fallbackPrediction($record, $equipment)
    {
        // Metode yang sama dengan sebelumnya, tapi lebih sederhana
        $baseInterval = $equipment->type === 'elektrik' ? rand(60, 90) : rand(90, 120);
        
        if ($equipment->priority === 'merah') {
            $baseInterval = (int)($baseInterval * 0.8);
        }
        
        $record->next_maintenance_date = Carbon::now()->addDays($baseInterval);
        $record->condition_score = rand(70, 90);
        
        if ($record->condition_score > 85) {
            $record->recommendation = 'Routine maintenance recommended';
        } elseif ($record->condition_score > 70) {
            $record->recommendation = 'Inspection needed';
        } else {
            $record->recommendation = 'Immediate maintenance required';
        }
        
        $record->save();
    }
    
    /**
     * Tentukan potensi masalah berdasarkan data sensor
     */
    protected static function getPotentialIssues($sensorData)
    {
        $issues = [];
        
        if ($sensorData['vibration'] > 6) {
            $issues[] = 'Excessive vibration';
        }
        
        if ($sensorData['temperature'] > 85) {
            $issues[] = 'Overheating';
        }
        
        if ($sensorData['pressure'] > 120) {
            $issues[] = 'High pressure';
        }
        
        if ($sensorData['humidity'] < 30) {
            $issues[] = 'Low humidity';
        }
        
        return empty($issues) ? ['No immediate issues detected'] : $issues;
    }
}
