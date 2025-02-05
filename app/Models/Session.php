<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    // create the model of migration session 
    protected $fillable = [
        'course_id',
        'schedule_id',
        'start_Time',
        'end_time',
    ];


    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function attendance(){
        return $this->hasMany(Attendance::class);
    }
}
