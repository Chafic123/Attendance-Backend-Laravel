<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'DayOfWeek',
        'Date',
    ];

    public function course_sessions()
    {
        return $this->hasMany(CourseSession::class);
    }
}
