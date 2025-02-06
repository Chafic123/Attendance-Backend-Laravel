<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\Session;
class Course extends Model
{
    protected $fillable = [
        'name',
        'Room',
        'Section',
    ];
    // create the relation with the student 
    public function course_sessions()
    {
        return $this->hasMany(CourseSession::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_student')->withTimestamps();
    }

    public function instructors(){
        return $this->belongsToMany(Instructor::class, 'course_instructor')->withTimestamps();
    }

    public function term(){
        return $this->belongsToMany(Term::class);
    }

}
