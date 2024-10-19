<?php

namespace App\Models;

use App\Enums\DomainStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'screenshot', 'amount', 'skype_url', 'status', 'is_default'];

    protected function casts(): array
    {
        return [
            'status' => DomainStatus::class
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function websiteUrls(): HasMany
    {
        return $this->hasMany(WebsiteUrl::class);
    }
}
