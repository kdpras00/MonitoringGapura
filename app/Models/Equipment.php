<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'serial_number',
        'location',
        'installation_date',
        'description',
        'status',
        'manual_url',
        'specifications',
        'qr_code',
        'sop_url',
        'checklist',
    ];

    protected $table = 'equipments';

    protected $casts = [
        'checklist' => 'array',
    ];


    public function predictiveMaintenances()
    {
        return $this->hasMany(PredictiveMaintenance::class, 'equipment_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($equipment) {
            $equipment->qr_code = QrCode::size(200)->generate($equipment->name);
        });
    }

    public function generateQrCode(): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(
            QrCode::format('svg')->size(200)->generate($this->name)
        );
    }

    public function sensorData()
    {
        return $this->hasMany(SensorData::class);
    }
}
