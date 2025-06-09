<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceHistory extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'maintenance_id',
        'equipment_id',
        'status',
        'data',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];
    
    /**
     * Get the maintenance that owns the history record.
     */
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }
    
    /**
     * Get the equipment that the history record is related to.
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
} 