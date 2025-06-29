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

    public function comments()
    {
        return $this->hasMany(MaintenanceComment::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($maintenance) {
            if (is_null($maintenance->technician_id)) {
                $maintenance->technician_id = 1; // 👈 Default teknisi ID 1
            }
            
            // Sinkronkan jenis alat dan prioritas dari equipment
            if (!empty($maintenance->equipment_id)) {
                $equipment = Equipment::find($maintenance->equipment_id);
                if ($equipment) {
                    $maintenance->equipment_type = $equipment->type;
                    $maintenance->priority = $equipment->priority;
                }
            }
        });

        static::creating(function ($maintenance) {
            if ($maintenance->actual_date) {
                $maintenance->next_service_date = Carbon::parse($maintenance->actual_date)->addMonth();
            }
        });

        static::updating(function ($maintenance) {
            if ($maintenance->actual_date) {
                $maintenance->next_service_date = Carbon::parse($maintenance->actual_date)->addMonth();
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
    }

    /**
     * Get all maintenance history records for this maintenance.
     */
    public function history()
    {
        return $this->hasMany(MaintenanceHistory::class);
    }

    /**
     * Check if maintenance has been completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed'; // Status completed berarti sudah diverifikasi oleh supervisor
    }

    /**
     * Check if maintenance is in progress.
     */
    public function isInProgress()
    {
        return $this->status === 'in-progress';
    }

    /**
     * Check if maintenance is pending approval.
     */
    public function isPendingApproval()
    {
        return $this->status === 'pending'; // Status pending berarti menunggu approval dari supervisor
    }

    /**
     * Check if maintenance is waiting for verification.
     */
    public function isWaitingVerification()
    {
        return $this->status === 'pending'; // Status pending sama dengan menunggu verifikasi
    }

    /**
     * Check if maintenance has been approved.
     */
    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Check if maintenance has been rejected.
     */
    public function isRejected()
    {
        return $this->approval_status === 'rejected';
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
     * Get all inspection records for this maintenance.
     */
    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'technician_id', 'technician_id')
            ->where('equipment_id', $this->equipment_id);
    }
    
    /**
     * Get the latest inspection for this maintenance.
     */
    public function getLatestInspectionAttribute()
    {
        return $this->inspections()->latest()->first();
    }

    /**
     * Check if maintenance has an associated inspection.
     */
    public function hasInspection()
    {
        return $this->inspections()->exists();
    }
}
