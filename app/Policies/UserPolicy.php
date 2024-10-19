<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function view(User $user, User $model): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        if($user->isAdmin){
            return $model->team_id == $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if($user->isSuperAdmin || $user->isAdmin){
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        if($user->isAdmin){
            return $model->team_id == $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        if($user->isAdmin){
            return $model->team_id == $user->id;
        }

        return false;
    }

    public function changeStatus(User $user, User $model): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        if($user->isAdmin){
            return $model->team_id == $user->id;
        }

        return false;
    }

}
