<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentVisit extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'recorded_by',
        'parent_name',
        'visit_date',
        'purpose',
        'discussion_result',
        'note',
        'follow_up',
    ];

    protected function casts(): array
    {
        return ['visit_date' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
