<?php

namespace App\Models;

use Database\Factories\PublicDownloadRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicDownloadRequest extends Model
{
    /** @use HasFactory<PublicDownloadRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'learning_material_id',
        'requester_name',
        'requester_email',
        'access_token_hash',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function learningMaterial(): BelongsTo
    {
        return $this->belongsTo(LearningMaterial::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
