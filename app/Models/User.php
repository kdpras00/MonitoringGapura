<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;
use App\Models\Maintenance;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role', // Hanya perlu jika masih ingin pakai role manual
        'is_approved',
        'email_provider'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * Cek apakah user adalah admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user adalah teknisi.
     */
    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }

    /**
     * Cek apakah user adalah viewer.
     */
    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    /**
     * Cek apakah user adalah supervisor.
     */
    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor' || $this->hasRole('supervisor');
    }

    /**
     * Mutator: Ubah email menjadi lowercase sebelum disimpan.
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Mutator: Format nama agar selalu kapital di awal kata.
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucwords(strtolower($value));
    }

    /**
     * Mutator: Hash password sebelum disimpan.
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
        }
    }

    /**
     * Scope untuk mendapatkan semua teknisi.
     */
    public function scopeTechnician($query)
    {
        return $query->where('role', 'technician');
    }

    /**
     * Scope untuk mendapatkan teknisi yang sudah disetujui.
     */
    public function scopeApprovedTechnician($query)
    {
        return $query->where('role', 'technician')
            ->where('is_approved', true);
    }

    /**
     * Relasi ke maintenance (teknisi memiliki banyak maintenance).
     */
    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'technician_id');
    }

    /**
     * Mengembalikan chat ID Telegram untuk notifikasi.
     */
    public function routeNotificationForTelegram()
    {
        return $this->telegram_chat_id ?? null;
    }

    /**
     * Relasi langsung ke permissions (Opsional, bisa dihapus jika pakai HasRoles).
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
