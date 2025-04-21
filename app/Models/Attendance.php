<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'course_session_id',
        'is_present',
        'attended_at',
        'course_id' 
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');  
    }

    public function course_session()
    {
        return $this->belongsTo(CourseSession::class);
    }

    public function attendanceRequest()
    {
        return $this->hasMany(AttendanceRequest::class);
    }
}
