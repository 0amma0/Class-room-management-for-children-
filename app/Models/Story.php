<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'illustration_path',
        'illustration_model',
        'illustration_generated_at',
    ];

    protected function casts(): array
    {
        return ['illustration_generated_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
