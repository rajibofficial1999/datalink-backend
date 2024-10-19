<?php

namespace App\Traits\Users;

use App\Models\AccountInformation;
use App\Models\Domain;
use App\Models\Notice;
use App\Models\Order;
use App\Models\OtpCode;
use App\Models\Role;
use App\Models\Support;
use App\Models\User;
use App\Models\WebsiteUrl;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait Relations
{
    public function team(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(User::class, 'team_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(AccountInformation::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function supports(): HasMany
    {
        return $this->hasMany(Support::class);
    }

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class);
    }

    public function otpCode(): HasOne
    {
        return $this->hasOne(OtpCode::class);
    }

    public function urls(): HasManyThrough
    {
        return $this->hasManyThrough(WebsiteUrl::class, Domain::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
