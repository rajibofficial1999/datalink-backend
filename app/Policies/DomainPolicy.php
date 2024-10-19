<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\User;

class DomainPolicy
{
    public function view(User $user, Domain $domain): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        if($user->isAdmin){
            return $domain->user_id == $user->id;
        }

        return false;
    }

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
    public function update(User $user, Domain $domain): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        if($user->isAdmin){
            return $domain->user_id == $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Domain $domain): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        if($user->isAdmin){
            return $domain->user_id == $user->id;
        }

        return false;
    }

    public function updateDomainStatus(User $user): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        return false;
    }
}
