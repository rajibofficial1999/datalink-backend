<?php

namespace App\Services\AccountInformation;

use App\Models\AccountInformation;
use Illuminate\Http\Request;

class AccountUpdateService
{
    public function update(AccountInformation $account, Request $request): array
    {
        $data = $this->handlePhotoUpload($account, $request);

        $account->update($data);

        return [
            'success' => true,
            'status_code' => 200,
            'account' => $account,
            'account_access_token' => $account->access_token,
        ];
    }

    protected function handlePhotoUpload(AccountInformation $account, Request $request): array
    {
        $data = $request->all();
        $photoFields = ['nid_front', 'nid_back', 'selfie'];

        foreach ($photoFields as $field) {
            $data = $this->uploadPhoto($account, $request, $field, $data);
        }

        return $data;
    }

    protected function uploadPhoto(AccountInformation $account, Request $request, string $field, array $data): array
    {
        if ($request->hasFile($field)) {
            $path = $request->file($field)->store('accounts', 'public');
            $data[$field] = $path;
            $account->deleteOlderPhoto($account->{$field} ?? '');
        }

        return $data;
    }
}
