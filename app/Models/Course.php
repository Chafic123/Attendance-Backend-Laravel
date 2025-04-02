<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\Session;
use Carbon\Carbon;

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

    public function getStartTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function getEndTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function course_sessions()
    {
        return $this->hasMany(CourseSession::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_student')
        ->withPivot('enrollment-date')
        ->withTimestamps();
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

    public function attendanceRequests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }
}
