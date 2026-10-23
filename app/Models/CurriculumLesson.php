<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurriculumLesson extends Model
{
    protected $fillable = ['grade_id', 'lesson_number', 'title'];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(LessonSection::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ClassCurriculumProgress::class);
    }

    public function prayers(): BelongsToMany
    {
        return $this->belongsToMany(Prayer::class, 'lesson_prayers')->withPivot('note');
    }

    public function songs(): BelongsToMany
    {
        return $this->belongsToMany(Song::class, 'lesson_songs')->withPivot('note');
    }

    public function quotes(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class, 'lesson_quotes')->withPivot('vocabulary_notes');
    }
}
