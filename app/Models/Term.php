<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    protected $fillable = [
        'name',
        'year',
        'start_time',
        'end_time',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }
}
