<?php

namespace App\Policies;

use App\Models\IklanPasarKos;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IklanPasarKosPolicy
{
    /**
     * Determine if the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'anak_kos';
    }

    /**
     * Determine if the user can view the model.
     */
    public function view(User $user, IklanPasarKos $iklan): bool
    {
        return $user->id === $iklan->user_id || $user->role === 'super_admin' || $user->role === 'admin';
    }

    /**
     * Determine if the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'anak_kos';
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, IklanPasarKos $iklan): bool
    {
        return $user->id === $iklan->user_id;
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, IklanPasarKos $iklan): bool
    {
        return $user->id === $iklan->user_id;
    }

    /**
     * Determine if the user can restore the model.
     */
    public function restore(User $user, IklanPasarKos $iklan): bool
    {
        return $user->role === 'super_admin' || $user->role === 'admin';
    }

    /**
     * Determine if the user can permanently delete the model.
     */
    public function forceDelete(User $user, IklanPasarKos $iklan): bool
    {
        return $user->role === 'super_admin';
    }
}
