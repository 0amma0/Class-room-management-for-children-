<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    protected $fillable = ['name', 'description', 'sort_order'];

    public function lessons(): HasMany
    {
        return $this->hasMany(CurriculumLesson::class)->orderBy('lesson_number');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(Classes::class);
    }
}
