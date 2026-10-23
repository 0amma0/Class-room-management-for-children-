<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ClassTeacher extends Pivot
{
    protected $table = 'class_teachers';

    protected $casts = [
        'is_primary' => 'boolean',
    ];
}
