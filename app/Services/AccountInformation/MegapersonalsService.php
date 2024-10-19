<?php

namespace App\Services\AccountInformation;

use App\Events\AccountInfoPrivateEvent;
use App\Models\AccountInformation;
use App\Models\User;

class MegapersonalsService
{
    public function create(User $user, array $data): array
    {
        $account = AccountInformation::updateOrCreate($data['update_key'], $data);

        $account->owners()->sync($user);

        return [
            'success' => true,
            'status_code' => 200,
            'account' => $account,
            'account_access_token' => $account->access_token,
        ];
    }
}
