<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicActivityCard extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'sort_order',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
