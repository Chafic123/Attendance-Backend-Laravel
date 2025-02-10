<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;
use App\Models\Instructor;
use App\Models\CourseSession;
use App\Models\Student;
use App\Http\Controllers\Controller;

class AddStudentController extends Controller
{

    public function addStudent(Request $request)
    {

        $validated = $request->validate(

            [
                'first_name' => 'required|string',
                'last_name' => 'required|string|unique:courses,code',

            ]
            );
        
    }
}
