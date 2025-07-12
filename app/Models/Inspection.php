<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Maintenance;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Models\Traits\StatusSafety;

class Inspection extends Model
{
    use HasFactory, StatusSafety;

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';     // Belum dimulai
    const STATUS_IN_PROGRESS = 'in-progress'; // Sedang dikerjakan (baru upload foto sebelum)
    const STATUS_PENDING_VERIFICATION = 'pending-verification'; // Menunggu verifikasi (foto sebelum dan sesudah)
    const STATUS_VERIFIED = 'verified';   // Sudah diverifikasi oleh supervisor
    const STATUS_REJECTED = 'rejected';   // Ditolak oleh supervisor

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'equipment_id',
        'maintenance_id',
        'technician_id',
        'inspection_date',
        'schedule_date',
        'status',
        'notes',
        'before_image',
        'after_image',
        'checklist',
        'location',
        'location_lat',
        'location_lng',
        'location_timestamp',
        'completion_date',
        'verification_notes',
        'verification_date',
        'verified_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'inspection_date' => 'datetime',
        'schedule_date' => 'datetime',
        'completion_date' => 'datetime',
        'location_timestamp' => 'datetime',
        'verification_date' => 'datetime',
        'checklist' => 'array',
    ];

    /**
     * Boot method untuk model
     */
    protected static function boot()
    {
        parent::boot();

        // Observer untuk status changes
        static::creating(function ($inspection) {
            // Pastikan tidak ada duplikat inspeksi untuk kombinasi equipment_id, technician_id, dan status=pending
            if ($inspection->status === 'pending') {
                $existingInspection = self::where('equipment_id', $inspection->equipment_id)
                    ->where('technician_id', $inspection->technician_id)
                    ->where('status', 'pending')
                    ->first();
                
                if ($existingInspection) {
                    // Jika sudah ada, gunakan yang sudah ada
                    return false;
                }
            }
            
            // Pastikan tidak ada duplikat inspeksi untuk maintenance_id yang sama
            if ($inspection->maintenance_id) {
                $existingInspection = self::where('maintenance_id', $inspection->maintenance_id)
                    ->first();
                    
                if ($existingInspection) {
                    // Jika maintenance_id sudah digunakan di inspeksi lain
                    \Log::warning('Mencoba membuat inspeksi duplikat untuk maintenance_id: ' . $inspection->maintenance_id);
                    return false;
                }
            }
        });
        
        static::updating(function ($inspection) {
            if (isset($inspection->attributes['status']) &&
                isset($inspection->original['status']) &&
                $inspection->attributes['status'] != $inspection->original['status']) {

                \Log::info('Inspection status changing', [
                    'id' => $inspection->id,
                    'from' => $inspection->original['status'],
                    'to' => $inspection->attributes['status']
                ]);
            }
        });

        static::updated(function ($inspection) {
            if ($inspection->isDirty('status')) {
                \Log::info('Inspection status changed', [
                    'id' => $inspection->id,
                    'status' => $inspection->status,
                    'dirty' => $inspection->isDirty(),
                    'changes' => $inspection->getChanges()
                ]);
            }
        });
    }

    /**
     * Override setAttribute untuk status
     */
    public function setAttribute($key, $value)
    {
        // Khusus untuk kolom status
        if ($key === 'status') {
            // Pastikan value sama persis dengan salah satu nilai enum yang valid
            $validValues = [
                self::STATUS_PENDING, 
                self::STATUS_IN_PROGRESS,
                self::STATUS_PENDING_VERIFICATION,
                self::STATUS_VERIFIED, 
                self::STATUS_REJECTED
            ];

            if (!in_array($value, $validValues)) {
                // Convert unquoted/invalid status to valid status
                if (strtolower($value) == 'pending' || $value === self::STATUS_PENDING) {
                    $value = self::STATUS_PENDING;
                }
                elseif (strtolower($value) == 'in-progress' || $value === self::STATUS_IN_PROGRESS) {
                    $value = self::STATUS_IN_PROGRESS;
                }
                elseif (strtolower($value) == 'pending-verification' || $value === self::STATUS_PENDING_VERIFICATION) {
                    $value = self::STATUS_PENDING_VERIFICATION;
                }
                elseif (strtolower($value) == 'verified' || $value === self::STATUS_VERIFIED) {
                    $value = self::STATUS_VERIFIED;
                }
                elseif (strtolower($value) == 'rejected' || $value === self::STATUS_REJECTED) {
                    $value = self::STATUS_REJECTED;
                }
                else {
                    // Default value if not valid
                    $value = self::STATUS_PENDING;
                }
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Override save method to make sure status is properly set
     */
    public function save(array $options = [])
    {
        // Sebelum save, pastikan status adalah nilai yang valid
        if (isset($this->attributes['status'])) {
            $status = $this->attributes['status'];
            $validValues = [
                self::STATUS_PENDING, 
                self::STATUS_IN_PROGRESS,
                self::STATUS_PENDING_VERIFICATION,
                self::STATUS_VERIFIED, 
                self::STATUS_REJECTED
            ];

            if (!in_array($status, $validValues)) {
                // Jika tidak valid, konversi ke valid status
                if (strcasecmp($status, 'pending') == 0) {
                    $this->attributes['status'] = self::STATUS_PENDING;
                }
                elseif (strcasecmp($status, 'in-progress') == 0) {
                    $this->attributes['status'] = self::STATUS_IN_PROGRESS;
                }
                elseif (strcasecmp($status, 'pending-verification') == 0) {
                    $this->attributes['status'] = self::STATUS_PENDING_VERIFICATION;
                }
                elseif (strcasecmp($status, 'verified') == 0) {
                    $this->attributes['status'] = self::STATUS_VERIFIED;
                }
                elseif (strcasecmp($status, 'rejected') == 0) {
                    $this->attributes['status'] = self::STATUS_REJECTED;
                }
                else {
                    // Default value if not valid
                    $this->attributes['status'] = self::STATUS_PENDING;
                }
            }
        }

        return parent::save($options);
    }

    /**
     * Override the attributes getter for status
     *
     * @param  string|null  $value
     * @return string
     */
    public function getStatusAttribute($value)
    {
        // Pastikan status selalu salah satu dari nilai yang valid
        $validStatuses = [
            self::STATUS_PENDING, 
            self::STATUS_IN_PROGRESS,
            self::STATUS_PENDING_VERIFICATION,
            self::STATUS_VERIFIED, 
            self::STATUS_REJECTED
        ];

        if (is_null($value) || !in_array($value, $validStatuses)) {
            return self::STATUS_PENDING;
        }

        return $value;
    }

    /**
     * Debug method untuk menampilkan status
     *
     * @return string
     */
    public function debugStatus()
    {
        return "Current status: '{$this->status}', Database type: " . gettype($this->status) . ", Original: " . json_encode($this->getOriginal('status'));
    }

    /**
     * Get the maintenance that this inspection belongs to.
     */
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    /**
     * Get the equipment that this inspection belongs to.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

    /**
     * Get the technician who performed this inspection.
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Get the supervisor who verified this inspection.
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the related maintenance tasks.
     */
    public function maintenances()
    {
        return Maintenance::where('equipment_id', $this->equipment_id)
            ->where('technician_id', $this->technician_id)
            ->latest()
            ->get();
    }

    /**
     * Get the most recent related maintenance task.
     */
    public function getLatestMaintenanceAttribute()
    {
        return Maintenance::where('equipment_id', $this->equipment_id)
            ->where('technician_id', $this->technician_id)
            ->latest()
            ->first();
    }

    /**
     * Check if inspection is still pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if inspection is in progress.
     */
    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if inspection is pending verification.
     */
    public function isPendingVerification()
    {
        return $this->status === self::STATUS_PENDING_VERIFICATION;
    }

    /**
     * Check if inspection has been verified.
     */
    public function isVerified()
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    /**
     * Check if inspection has been rejected.
     */
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get URL for before image.
     */
    public function getBeforeImageUrlAttribute()
    {
        if (!$this->before_image) {
            return null;
        }

        return Storage::disk('public')->url($this->before_image);
    }

    /**
     * Get URL for after image.
     */
    public function getAfterImageUrlAttribute()
    {
        if (!$this->after_image) {
            return null;
        }

        return Storage::disk('public')->url($this->after_image);
    }

    /**
     * Method aman untuk memverifikasi inspeksi
     *
     * @param string $notes
     * @param int $userId
     * @return bool
     */
    public function verify($notes, $userId)
    {
        $this->status = self::STATUS_VERIFIED;
        $this->verification_notes = $notes;
        $this->verification_date = now();
        $this->verified_by = $userId;
        return $this->save();
    }

    /**
     * Method aman untuk menolak inspeksi
     *
     * @param string $notes
     * @param int $userId
     * @return bool
     */
    public function reject($notes, $userId)
    {
        $this->status = self::STATUS_REJECTED;
        $this->verification_notes = $notes;
        $this->verification_date = now();
        $this->verified_by = $userId;
        return $this->save();
    }

    /**
     * Method aman untuk mengembalikan ke pending
     *
     * @return bool
     */
    public function returnToPending()
    {
        $this->status = self::STATUS_PENDING;
        $this->completion_date = null;
        return $this->save();
    }
}
