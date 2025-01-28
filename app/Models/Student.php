<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'major',
        'phone_number',
        'address',
        'image',
        'video',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // public function courses()
    // {
    //     return $this->belongsToMany(Course::class, 'enrollment', 'student_id', 'course_id')
    //                 ->withTimestamps();
    // }

    // public function attendance()
    // {
    //     return $this->hasMany(Attendance::class);
    // }

    // public function attendanceRequests()
    // {
    //     return $this->hasMany(AttendanceRequest::class);
    // }

    // public function notifications()
    // {
    //     return $this->hasMany(Notification::class);
    // }
}
