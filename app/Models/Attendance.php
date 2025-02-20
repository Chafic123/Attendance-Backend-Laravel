<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'session_id',
        'is_present',
        'attended_at'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
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
