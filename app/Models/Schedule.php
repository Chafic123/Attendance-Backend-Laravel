<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'DayOfWeek',
        'Date',
    ];

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }


}
