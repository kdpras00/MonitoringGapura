<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorPNG;

class Equipment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'equipments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'equipment_id',
        'name',
        'type',
        'location',
        'status',
        'priority',
        'last_maintenance_date',
        'next_maintenance_date',
        'barcode',
        'serial_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'checklist' => 'array',
    ];

    /**
     * Get all maintenance records for this equipment.
     */
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class);
    }

    /**
     * Get the latest maintenance record for this equipment.
     */
    public function latestMaintenance()
    {
        return $this->maintenances()->latest()->first();
    }

    /**
     * Get active maintenance records for this equipment.
     */
    public function activeMaintenance()
    {
        return $this->maintenances()
            ->where('status', 'scheduled')
            ->orWhere(function($query) {
                $query->where('status', 'completed')
                      ->where('approval_status', 'pending');
            })
            ->latest()
            ->first();
    }

    public function predictiveMaintenances()
    {
        return $this->hasMany(PredictiveMaintenance::class, 'equipment_id');
    }

    /**
     * Get predictive maintenance for this equipment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function predictiveMaintenance()
    {
        return $this->hasOne(PredictiveMaintenance::class);
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
            
            // Generate barcode if not provided
            if (empty($equipment->barcode)) {
                $equipment->barcode = 'EQ' . str_pad($equipment->id ?? random_int(1000, 9999), 8, '0', STR_PAD_LEFT);
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
    
    /**
     * Generate HTML barcode.
     *
     * @return string
     */
    public function getHtmlBarcode()
    {
        $generator = new BarcodeGeneratorHTML();
        return $generator->getBarcode($this->barcode ?? $this->serial_number, $generator::TYPE_CODE_128, 2, 50);
    }
    
    /**
     * Generate PNG barcode.
     *
     * @return string
     */
    public function getPngBarcode()
    {
        $generator = new BarcodeGeneratorPNG();
        return 'data:image/png;base64,' . base64_encode(
            $generator->getBarcode($this->barcode ?? $this->serial_number, $generator::TYPE_CODE_128, 2, 50)
        );
    }
    
    /**
     * Get URL for scanning barcode.
     *
     * @return string
     */
    public function getBarcodeUrl()
    {
        return url('/equipment/scan?code=' . ($this->barcode ?: $this->serial_number));
    }

    public function sensorData()
    {
        return $this->hasMany(SensorData::class);
    }
    
    /**
     * Get checklist as array.
     *
     * @return array
     */
    public function getChecklistArrayAttribute()
    {
        try {
            // Jika checklist kosong atau null
            if (empty($this->attributes['checklist'])) {
                return [];
            }
            
            // Log tipe data untuk debugging
            \Illuminate\Support\Facades\Log::debug('Checklist type for equipment #' . $this->id . ': ' . gettype($this->attributes['checklist']));
            if (is_string($this->attributes['checklist'])) {
                \Illuminate\Support\Facades\Log::debug('Checklist content: ' . substr($this->attributes['checklist'], 0, 100) . '...');
            }
            
            // Jika checklist sudah berupa array
            if (is_array($this->attributes['checklist'])) {
                return $this->attributes['checklist'];
            }
            
            // Jika checklist berupa string JSON
            if (is_string($this->attributes['checklist'])) {
                // Decode JSON string
                $decoded = json_decode($this->attributes['checklist'], true);
                
                // Jika hasil decode valid dan berupa array
                if (is_array($decoded)) {
                    return $decoded;
                }
                
                // Jika string bukan JSON valid, coba split berdasarkan baris baru
                if (strpos($this->attributes['checklist'], "\n") !== false) {
                    return array_filter(explode("\n", $this->attributes['checklist']));
                }
                
                // Jika string berisi koma, coba split berdasarkan koma
                if (strpos($this->attributes['checklist'], ',') !== false) {
                    return array_filter(explode(',', $this->attributes['checklist']));
                }
                
                // Jika semua opsi di atas gagal, mengembalikan string sebagai item tunggal dalam array
                return [$this->attributes['checklist']];
            }
            
            // Fallback untuk tipe data lain
            return [];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error saat konversi checklist menjadi array: ' . $e->getMessage(), [
                'equipment_id' => $this->id,
                'checklist_type' => gettype($this->attributes['checklist']),
                'checklist' => is_string($this->attributes['checklist']) ? substr($this->attributes['checklist'], 0, 200) : null,
                'stack_trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Get checklist attribute.
     * Memastikan bahwa akses langsung ke property checklist juga mengembalikan array.
     *
     * @param  mixed  $value
     * @return array
     */
    public function getChecklistAttribute($value)
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
                return [$value];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error decoding checklist: ' . $e->getMessage());
                return [];
            }
        }
        
        return [];
    }

    /**
     * Set checklist attribute.
     * Memastikan bahwa checklist disimpan dalam format JSON.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setChecklistAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['checklist'] = json_encode([]);
        } elseif (is_array($value)) {
            $this->attributes['checklist'] = json_encode($value);
        } elseif (is_string($value) && !empty($value)) {
            try {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $this->attributes['checklist'] = $value;
                } else {
                    $this->attributes['checklist'] = json_encode([$value]);
                }
            } catch (\Exception $e) {
                $this->attributes['checklist'] = json_encode([$value]);
            }
        } else {
            $this->attributes['checklist'] = json_encode([]);
        }
    }

    /**
     * Get all inspection records for this equipment.
     */
    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }
    
    /**
     * Get the latest inspection for this equipment.
     */
    public function latestInspection()
    {
        return $this->inspections()->latest()->first();
    }
}
