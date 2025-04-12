<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseInstructor extends Model
{
    protected $table = 'course_instructor'; 

    protected $fillable = [
        'course_id',
        'instructor_id',
        'status',
        'enrollment-date',
    ];

    public $timestamps = true; 

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }
}
