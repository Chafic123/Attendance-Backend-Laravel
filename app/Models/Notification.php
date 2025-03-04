<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'instructor_id',
        'student_id',
        'message',
        'type',
        'read_status', 
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
