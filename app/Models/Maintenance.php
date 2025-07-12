<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\MaintenanceComment;
use Carbon\Carbon;

class Maintenance extends Model
{
    use HasFactory;

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';     // Jadwal sudah disusun, menunggu penugasan teknisi
    const STATUS_ASSIGNED = 'assigned';   // Teknisi sudah ditugaskan
    const STATUS_IN_PROGRESS = 'in-progress'; // Sedang dikerjakan (foto sebelum)
    const STATUS_PENDING_VERIFICATION = 'pending-verification'; // Menunggu verifikasi (foto sesudah)
    const STATUS_VERIFIED = 'verified';   // Sudah diverifikasi oleh supervisor
    const STATUS_REJECTED = 'rejected';   // Ditolak oleh supervisor

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'maintenances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'equipment_id',
        'equipment_name',
        'scheduled_date',
        'completion_date',
        'technician',
        'equipment_type',
        'priority',
        'notes',
        'before_image',
        'before_image_time',
        'after_image',
        'after_image_time',
        'checklist',
        'status',
        'duration',
        'location',
        'location_lat',
        'location_lng',
        'location_timestamp',
        'completion_notes',
        'result',
        'approval_status',
        'approval_notes',
        'approved_by',
        'approval_date',
        'schedule_date',
        'actual_date',
        'next_service_date',
        'technician_id',
        'maintenance_type',
        'cost',
        'description',
        'attachments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'scheduled_date' => 'datetime',
        'completion_date' => 'datetime',
        'before_image_time' => 'datetime',
        'after_image_time' => 'datetime',
        'location_timestamp' => 'datetime',
        'approval_date' => 'datetime',
        'schedule_date' => 'datetime',
        'actual_date' => 'datetime',
        'next_service_date' => 'datetime',
        'checklist' => 'array',
    ];

    /**
     * The equipment that this maintenance belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function comments()
    {
        return $this->hasMany(MaintenanceComment::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($maintenance) {
            // Sinkronkan jenis alat dan prioritas dari equipment
            if (!empty($maintenance->equipment_id)) {
                $equipment = Equipment::find($maintenance->equipment_id);
                if ($equipment) {
                    $maintenance->equipment_type = $equipment->type;
                    $maintenance->priority = $equipment->priority;
                }
            }
            
            // Set jadwal service berikutnya otomatis 3 bulan setelah jadwal maintenance
            if ($maintenance->schedule_date && empty($maintenance->next_service_date)) {
                $maintenance->next_service_date = Carbon::parse($maintenance->schedule_date)->addMonths(3);
            }
        });

        static::updating(function ($maintenance) {
            // Update jadwal service berikutnya jika jadwal maintenance berubah
            if ($maintenance->isDirty('schedule_date')) {
                $maintenance->next_service_date = Carbon::parse($maintenance->schedule_date)->addMonths(3);
            }
            
            // Jika tanggal aktual diisi, update jadwal service berikutnya
            if ($maintenance->actual_date) {
                $maintenance->next_service_date = Carbon::parse($maintenance->actual_date)->addMonths(3);
            }
            
            // Sinkronkan jenis alat dan prioritas dari equipment saat update
            if (!empty($maintenance->equipment_id) && 
                (empty($maintenance->equipment_type) || empty($maintenance->priority))) {
                $equipment = Equipment::find($maintenance->equipment_id);
                if ($equipment) {
                    $maintenance->equipment_type = $equipment->type;
                    $maintenance->priority = $equipment->priority;
                }
            }
        });
        
        // Log saat maintenance dihapus
        static::deleting(function ($maintenance) {
            \Log::info('Maintenance being deleted', [
                'id' => $maintenance->id,
                'equipment_id' => $maintenance->equipment_id,
                'technician_id' => $maintenance->technician_id
            ]);
            
            // Hapus inspeksi terkait saat maintenance dihapus
            $maintenance->inspections()->delete();
        });
    }

    /**
     * Get all maintenance history records for this maintenance.
     */
    public function history()
    {
        return $this->hasMany(MaintenanceHistory::class);
    }

    /**
     * Check if maintenance has been verified.
     */
    public function isVerified()
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    /**
     * Check if maintenance is in progress.
     */
    public function isInProgress()
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if maintenance is pending approval.
     */
    public function isPendingApproval()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if maintenance is planned.
     */
    public function isPlanned()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if maintenance is assigned.
     */
    public function isAssigned()
    {
        return $this->status === self::STATUS_ASSIGNED;
    }

    /**
     * Check if maintenance is waiting for verification.
     */
    public function isWaitingVerification()
    {
        return $this->status === self::STATUS_PENDING_VERIFICATION;
    }

    /**
     * Check if maintenance has been rejected.
     */
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED || $this->approval_status === 'rejected';
    }

    /**
     * Check if maintenance has been approved.
     */
    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Get schedule_date as Carbon instance.
     *
     * @return \Carbon\Carbon
     */
    public function getScheduleDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    /**
     * Get actual_date as Carbon instance.
     *
     * @return \Carbon\Carbon
     */
    public function getActualDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    /**
     * Get next_service_date as Carbon instance.
     *
     * @return \Carbon\Carbon
     */
    public function getNextServiceDateAttribute($value)
    {
        return $value ? Carbon::parse($value) : null;
    }

    /**
     * Get inspections related to this maintenance.
     */
    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    /**
     * Get the most recent inspection for this maintenance.
     */
    public function getLatestInspectionAttribute()
    {
        return $this->inspections()->latest()->first();
    }

    /**
     * Check if this maintenance has inspections.
     */
    public function hasInspection()
    {
        return $this->inspections()->count() > 0;
    }
}
