<?php

namespace App\Policies;

use App\Models\AccountInformation;
use App\Models\User;

class AccountInformationPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AccountInformation $accountInformation): bool
    {
        if ($user->isSuperAdmin) {
            return true;
        }

        if ($user->isAdmin) {
            $owner = $accountInformation->owner;

            return $owner->is($user) || $owner->team_id == $user->id;
        }

        return false;
    }

}
