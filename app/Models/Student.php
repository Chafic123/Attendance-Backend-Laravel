<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Storage;

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
        'processed_video',
        'personal_email',
    ];

    protected static function booted()
    {
        static::creating(function ($student) {
            $currentYear = date('Y');
            $studentCount = Student::where('student_id', 'LIKE', "$currentYear%")->count() + 1;
            $student->student_id = $currentYear . str_pad($studentCount, 4, '0', STR_PAD_LEFT);
        });
    }

    public function updateStudentDetails(array $data)
    {
        if (isset($data['phone'])) {
            $this->phone = $data['phone'];
        }
    
        if (isset($data['image']) && $data['image']) {
            if ($this->image) {
                Storage::delete('public/' . $this->image);
            }
            $this->image = $data['image']->store('students/images', 'public');
        }
    
        if (isset($data['video']) && $data['video']) {
            if ($this->video) {
                Storage::delete('public/' . $this->video);
            }
            $this->video = $data['video']->store('students/videos', 'public');
        }
    
        try {
            $this->save();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save details: ' . $e->getMessage()], 500);
        }
        
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

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_student', 'student_id', 'course_id');
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
