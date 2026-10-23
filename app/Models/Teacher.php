<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_code',
        'full_name',
        'nickname',
        'phone',
        'address',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Teacher $teacher): void {
            if (Schema::hasColumn('teachers', 'teacher_code') && blank($teacher->teacher_code)) {
                $teacher->teacher_code = 'TCH-'.Str::upper((string) Str::ulid());
            }
        });
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classes::class, 'class_teachers', 'teacher_id', 'class_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
