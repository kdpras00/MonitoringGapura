<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_id',
        'user_id',
        'comment',
    ];

    // Relasi ke Maintenance
    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
