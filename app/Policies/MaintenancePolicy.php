<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Maintenance;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaintenancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true; // Semua pengguna dapat melihat daftar maintenance
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Maintenance  $maintenance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Maintenance $maintenance)
    {
        return true; // Semua pengguna dapat melihat detail maintenance
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->role === 'admin'; // Hanya admin yang dapat membuat maintenance
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Maintenance  $maintenance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Maintenance $maintenance)
    {
        // Admin dapat mengupdate semua maintenance
        if ($user->role === 'admin') {
            return true;
        }
        
        // Teknisi hanya dapat mengupdate maintenance yang ditugaskan kepadanya
        if ($user->role === 'technician') {
            return $maintenance->technician_id === $user->id;
        }
        
        // Supervisor dapat mengupdate maintenance untuk verifikasi
        if ($user->role === 'supervisor') {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Maintenance  $maintenance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Maintenance $maintenance)
    {
        return $user->role === 'admin'; // Hanya admin yang dapat menghapus maintenance
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Maintenance  $maintenance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Maintenance $maintenance)
    {
        return $user->role === 'admin'; // Hanya admin yang dapat memulihkan maintenance
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Maintenance  $maintenance
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Maintenance $maintenance)
    {
        return $user->role === 'admin'; // Hanya admin yang dapat menghapus permanen maintenance
    }
} 