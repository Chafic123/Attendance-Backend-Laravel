<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{

    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'name'
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
    public function instructors()
    {
        return $this->hasMany(Instructor::class);
    }
}
