<?php

namespace App\Policies;

use App\Models\Support;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SupportPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Support $support): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Support $support): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Support $support): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        return false;
    }

}
