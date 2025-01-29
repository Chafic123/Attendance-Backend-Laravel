<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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

    // for the student_id
    protected static function booted()
    {

        static::creating(function ($student) {
            $currentYear = Carbon::now()->year;
            $student->student_id = $currentYear . str_pad($student->id, 4, '0', STR_PAD_LEFT);
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

    // Other relationships (courses, attendance, etc.) can be added here as needed
}

