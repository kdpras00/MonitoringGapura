<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

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
        'installation_date' => 'date',
    ];

    protected function getChecklistAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : [];
    }

    // Helper method to safely get checklist as an array
    public function getChecklistArrayAttribute(): array
    {
        return $this->checklist;
    }

    public function predictiveMaintenances()
    {
        return $this->hasMany(PredictiveMaintenance::class, 'equipment_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($equipment) {
            // Generate a unique code for the equipment (format: SN-YYYY-XXX)
            if (empty($equipment->qr_code)) {
                $year = date('Y');
                $serialPrefix = Str::substr($equipment->serial_number, 0, 3);
                $uniqueCode = strtoupper($serialPrefix) . '-' . $year . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);

                // Store the unique code in qr_code field
                $equipment->qr_code = $uniqueCode;
            }
        });
    }

    public function generateQrCode(): string
    {
        // Generate QR code SVG that points to the public equipment view URL
        $url = url('/q/' . urlencode($this->qr_code));
        return 'data:image/svg+xml;base64,' . base64_encode(
            QrCode::format('svg')->size(200)->generate($url)
        );
    }

    public function getQrCodeImageAttribute(): string
    {
        // Generate QR code SVG that points to the public equipment view URL
        $url = url('/q/' . urlencode($this->qr_code));

        // If QR code is not working correctly, fallback to ID-based URL
        if (empty($this->qr_code)) {
            $url = url('/equipment/qr-id/' . $this->id);
        }

        return QrCode::size(200)->generate($url);
    }

    public function sensorData()
    {
        return $this->hasMany(SensorData::class);
    }
}
