<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'ip_address',
        'country',
        'city',
        'state_name',
        'zip_code',
        'user_agent',
        'site'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
