<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Maintenance;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class Inspection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'equipment_id',
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
     * Check if inspection has been completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if inspection is still pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    /**
     * Check if inspection has been verified.
     */
    public function isVerified()
    {
        return $this->status === 'verified';
    }
    
    /**
     * Check if inspection has been rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
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
} 