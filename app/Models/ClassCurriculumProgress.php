<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassCurriculumProgress extends Model
{
    protected $fillable = ['class_id', 'curriculum_lesson_id', 'status', 'completed_at'];

    protected function casts(): array
    {
        return ['completed_at' => 'date'];
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function curriculumLesson(): BelongsTo
    {
        return $this->belongsTo(CurriculumLesson::class);
    }
}
