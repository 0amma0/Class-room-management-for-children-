<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonSection extends Model
{
    protected $fillable = [
        'curriculum_lesson_id',
        'section_code',
        'heading',
        'intro_text',
        'is_included',
    ];

    protected function casts(): array
    {
        return ['is_included' => 'boolean'];
    }

    public function curriculumLesson(): BelongsTo
    {
        return $this->belongsTo(CurriculumLesson::class);
    }
}
