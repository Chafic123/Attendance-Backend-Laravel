<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseSession extends Model
{
    protected $table = 'course_sessions';

    protected $fillable = [
        'course_id',
        'date'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
    

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
