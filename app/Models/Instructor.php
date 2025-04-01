<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;

    /**
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'department_id',
        'phone_number',
        'image',
        'personal_email',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_instructor')->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
