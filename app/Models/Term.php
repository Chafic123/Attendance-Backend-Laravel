<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    protected $fillable = [
        'name',
        'year',
        'start_date',
        'end_date',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }
}
