<?php

namespace App\Models;

use App\Enums\Package;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $appends = ['placed_time'];

    protected $fillable = [
        'user_id',
        'package',
        'amount',
        'period',
        'payment_screenshot',
        'status'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'package' => Package::class
        ];
    }

    public function placedTime(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->updated_at->format('d/M/Y')
        );
    }
}
