<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebsiteUrl;

class WebsiteUrlPolicy
{

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
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WebsiteUrl $websiteUrl): bool
    {
        if($user->isSuperAdmin){
            return true;
        }

        return false;
    }
}
