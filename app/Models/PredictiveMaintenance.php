<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PredictiveMaintenance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'equipment_id',
        'last_maintenance_date',
        'next_maintenance_date',
        'condition_score',
        'recommendation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_maintenance_date' => 'datetime',
        'next_maintenance_date' => 'datetime',
        'condition_score' => 'float',
    ];

    /**
     * Get the equipment that owns this predictive maintenance.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
    
    /**
     * Get the calendar event data for this predictive maintenance.
     *
     * @return array
     */
    public function toCalendarEvent(): array
    {
        // Tentukan warna berdasarkan condition score
        $color = match (true) {
            $this->condition_score >= 80 => '#34D399', // Green-400
            $this->condition_score >= 60 => '#FCD34D', // Amber-300
            default => '#F87171', // Red-400
        };
        
        $equipmentName = $this->equipment ? $this->equipment->name : 'Unknown Equipment';
        
        return [
            'id' => 'pred-' . $this->id,
            'title' => "Prediksi: {$equipmentName}",
            'start' => $this->next_maintenance_date->toIso8601String(),
            'end' => $this->next_maintenance_date->copy()->addHours(2)->toIso8601String(),
            'url' => route('filament.admin.resources.equipments.edit', $this->equipment_id),
            'color' => $color,
            'textColor' => '#000000',
            'description' => "Equipment: {$equipmentName}\nSkor Kondisi: {$this->condition_score}%\n{$this->recommendation}",
            'allDay' => true,
            'classNames' => ['predictive-maintenance-event'],
        ];
    }
    
    /**
     * Get all predictive maintenances as calendar events.
     *
     * @return array
     */
    public static function getAllCalendarEvents(): array
    {
        return static::with('equipment')
            ->get()
            ->map(fn($pm) => $pm->toCalendarEvent())
            ->toArray();
    }
}
