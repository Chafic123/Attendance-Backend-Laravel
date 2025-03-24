<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CourseStudent extends Pivot
{
    protected $table = 'course_student';  // Explicitly define the pivot table name

    protected $fillable = [
        'course_id',
        'student_id'
    ];

    public $timestamps = false; // Disable timestamps if not needed

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
