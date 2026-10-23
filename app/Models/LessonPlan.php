<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonPlan extends Model
{
    protected $fillable = [
        'class_id',
        'plan_type',
        'curriculum_lesson_id',
        'created_by',
        'execution_date',
        'level',
        'student_count',
        'theme',
        'objectives',
        'program',
        'materials',
        'status',
        'teacher_note',
        'closing_note',
        'catch_up_notes',
        'reminder_days',
        'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'execution_date' => 'date',
            'student_count' => 'integer',
            'reminder_days' => 'integer',
            'reminder_sent_at' => 'datetime',
            'program' => 'array',
            'materials' => 'array',
        ];
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function curriculumLesson(): BelongsTo
    {
        return $this->belongsTo(CurriculumLesson::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function googleCalendarUrl(): string
    {
        $start = $this->execution_date->format('Ymd');
        $end = $this->execution_date->copy()->addDay()->format('Ymd');

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => $this->theme,
            'dates' => $start.'/'.$end,
            'details' => $this->objectives ?? '',
        ], '', '&', PHP_QUERY_RFC3986);
    }
}
