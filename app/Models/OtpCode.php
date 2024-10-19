<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'code', 'token', 'is_valid', 'used_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function findOTPCodeByCode(string $code): ?OtpCode
    {
        return self::whereCode($code)->first();
    }

    public function isValid(): bool
    {
        return $this->is_valid;
    }

    public function isUsed(): bool
    {
        return $this->used_at ?? false;
    }

    public function isExpired($expirationMinutes = 3): bool
    {
        return $this->updated_at->addMinutes($expirationMinutes)->isPast();
    }
}
