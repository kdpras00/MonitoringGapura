<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PredictiveMaintenance extends Model
{
    protected $fillable = [
        'equipment_id',
        'last_maintenance_date',
        'next_maintenance_date',
        'condition_score',
        'recommendation',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
