<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{

    protected $fillable = [
        'student_id',
        'attendance_id',
        'course_id',
        'reason',
        'request_date',
        'instructor_id',
        'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
}
