<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'body'];

    protected $appends = ['time', 'human_time'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function time(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->created_at->format('d/m/d H:i a')
        );
    }

    public function humanTime(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->created_at->diffForHumans()
        );
    }
}
