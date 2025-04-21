<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CourseStudent;
use App\Models\Student;

class CourseSession extends Model
{
    protected $table = 'course_sessions';

    protected $fillable = [
        'course_id',
        'date'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function students()
    {
        return $this->hasManyThrough(
            Student::class,    // target (Student)
            CourseStudent::class,  // Intermediate (Pivot table)
            'course_id',   // Foreign key on course_student (points to courses)
            'id',          // Primary key on students
            'course_id',   // Local key on course_sessions (to find course)
            'student_id'   // Foreign key on course_student (points to students)
        );
    }
}
