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

    protected $table = 'maintenances';

    protected $fillable = [
        'name',
        'description',
        'status',
        'equipment_id',
        'schedule_date',
        'actual_date',
        'technician_id',
        'maintenance_type',
        'status',
        'cost',
        'notes',
        'next_service_date',
        'attachments',
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
        'actual_date'   => 'datetime',
        'attachments'   => 'array',
    ];


    protected $attributes = [
        'status' => 'scheduled', // 👈 Tambahkan default
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
        });
    }
}
