<?php

namespace App\Models;

use Database\Factories\AppNotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    /** @use HasFactory<AppNotificationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'teacher_reflection_id',
        'public_download_request_id',
        'support_conversation_id',
        'title',
        'message',
        'read_at',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reflection(): BelongsTo
    {
        return $this->belongsTo(TeacherReflection::class, 'teacher_reflection_id');
    }

    public function publicDownloadRequest(): BelongsTo
    {
        return $this->belongsTo(PublicDownloadRequest::class);
    }

    public function supportConversation(): BelongsTo
    {
        return $this->belongsTo(SupportConversation::class);
    }
}
