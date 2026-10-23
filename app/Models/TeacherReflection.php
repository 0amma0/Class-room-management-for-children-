<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherReflection extends Model
{
    protected $fillable = [
        'teacher_id',
        'class_id',
        'lesson_plan_id',
        'reflection_date',
        'objectives_met',
        'plan_alignment',
        'what_went_well',
        'challenges',
        'improvement',
        'note',
    ];

    protected function casts(): array
    {
        return ['reflection_date' => 'date'];
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function lessonPlan(): BelongsTo
    {
        return $this->belongsTo(LessonPlan::class);
    }
}
