<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'access_token' => $this->access_token,
            'status' => $this->status,
            'is_admin' => $this->is_admin,
            'is_super_admin' => $this->is_super_admin,
            'is_user' => $this->is_user,
            'avatar' => $this->avatar,
            'two_step_auth' => (bool) $this->two_step_auth,
            'subscription_details' => $this->subscription_details
        ];
    }
}
