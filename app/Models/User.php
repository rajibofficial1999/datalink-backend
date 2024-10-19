<?php

namespace App\Models;

use App\Enums\Package;
use App\Enums\UserStatus;
use App\Traits\Users\HasRoles;
use App\Traits\Users\Relations;
use App\Traits\Users\SubscriptionDetails;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory,
        Notifiable,
        HasApiTokens,
        HasRoles,
        Relations,
        SubscriptionDetails;

    protected $fillable = [
        'name',
        'email',
        'access_token',
        'team_id',
        'password',
        'two_step_auth',
        'avatar',
        'email_verified_at',
        'subscribed_at',
        'expired_at',
        'package',
        'status'
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $appends = ['is_admin', 'is_super_admin', 'is_user', 'verified_date', 'subscription_details'];


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'package' => Package::class
        ];
    }

    /* Relationships */



    /* Custom Attributes */

    public function verifiedDate(): Attribute
    {
        return Attribute::make(
            get: fn() => Carbon::parse($this->email_verified_at)->format('d-M-y')
        );
    }

    public function isVerified(): bool
    {
        return (bool) $this->email_verified_at;
    }

    public function isTowStepAuthOn(): bool
    {
        return $this->two_step_auth;
    }

    public static function findUserByAccessToken($token): ?User
    {
        return self::where('access_token', $token)->first();
    }
}
