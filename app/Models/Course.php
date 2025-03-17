<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\Session;

class Course extends Model
{
    protected $fillable = [
        'name',
        'Code',
        'Room',
        'credits',
        'Section',
        'day_of_week',
        'start_time',
        'end_time',
    ];
    public function course_sessions()
    {
        return $this->hasMany(CourseSession::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_student')->withTimestamps();
    }

    public function instructors()
    {
        return $this->belongsToMany(Instructor::class, 'course_instructor')->withTimestamps();
    }

    public function terms()
    {
        return $this->belongsToMany(Term::class, 'course_term');
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
