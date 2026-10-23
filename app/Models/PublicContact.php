<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicContact extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'phone',
        'avatar_type',
    ];
}
