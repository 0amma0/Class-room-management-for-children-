<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassGalleryPhoto extends Model
{
    protected $fillable = [
        'class_id',
        'uploaded_by',
        'photo_date',
        'session_label',
        'caption',
        'file_path',
    ];

    protected function casts(): array
    {
        return ['photo_date' => 'date'];
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
