<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'inspection_date' => 'datetime',
        'completion_date' => 'datetime',
        'location_timestamp' => 'datetime',
        'checklist' => 'array',
    ];

    /**
     * Get the equipment that this inspection belongs to.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'equipment_id');
    }

    /**
     * Get the technician who performed this inspection.
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
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
} 