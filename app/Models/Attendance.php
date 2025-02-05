<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'session_id',
        'is_present'
    ];

    public function student(){
        return $this->belongsTo(Student::class);
    }

    public function session(){
        return $this->belongsTo(Session::class);
    }

    public function attendanceRequest(){
        return $this->hasMany(AttendanceRequest::class);
    }

}
