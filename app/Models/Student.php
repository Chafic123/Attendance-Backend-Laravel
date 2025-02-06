<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;

class Student extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'user_id',
        'department_id',
        'major',
        'phone_number',
        'student_id',
        'address',
        'image',
        'video',
    ];

    protected static function booted()
    {
        static::creating(function ($student) {
            $currentYear = date('Y');

            $studentCount = Student::count() + 1;
            $student->student_id = $currentYear . str_pad($studentCount, 4, '0', STR_PAD_LEFT);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function Courses()
    {
        return $this->belongsToMany(Course::class, 'course_student')->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function attendance_requests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }
}
