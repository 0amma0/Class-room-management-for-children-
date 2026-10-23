<?php

namespace App\Support;

use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class GuruClassAccess
{
    /**
     * @return Builder<Classes>
     */
    public function classesFor(User $user): Builder
    {
        return Classes::query()
            ->where('classes.status', 'active')
            ->whereHas('teachers', function (Builder $query) use ($user): void {
                $query->whereKey($user->teacher_id)
                    ->where('teachers.status', 'active');
            });
    }

    /**
     * @return Builder<Student>
     */
    public function studentsForClass(User $user, int $classId): Builder
    {
        abort_unless($this->classesFor($user)->whereKey($classId)->exists(), 403);

        return Student::query()
            ->where('class_id', $classId)
            ->where('status', 'active');
    }
}
