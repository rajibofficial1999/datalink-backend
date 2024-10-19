<?php

namespace App\Traits\Users;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasRoles
{
    public function isAdmin(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->roles->first()?->name === 'admin-user' ?? false
        );
    }

    public function isSuperAdmin(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->roles->first()?->name === 'super-admin' ?? false
        );
    }

    public function isUser(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->roles->first()?->name === 'normal-user' ?? false
        );
    }
}
