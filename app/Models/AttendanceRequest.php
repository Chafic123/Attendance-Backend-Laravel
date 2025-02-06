<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{

    protected $fillable = [
        'student_id',
        'attendance_id',
        'reason',
        'request_date'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
