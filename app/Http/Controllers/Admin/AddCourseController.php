<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;
use App\Models\Instructor;
use App\Models\CourseSession;
use App\Models\Student;
use App\Http\Controllers\Controller;

class AddCourseController extends Controller
{

    public function addCourse(Request $request)
    {

        $validated = $request->validate(

            [
                'name' => 'required|string',
                'code' => 'required|string|unique:courses,code',
                'room' => 'required|string',
                'section' => 'required|string',
                'instructor_email' => 'required|email|exists:instructors,email',
                'session_start_time' => 'required|date',
                'session_end_time' => 'required|date',
                'schedue' => 'required|array',
            ]
            );
            $instructor = Instructor::where('email', $validated['instructor_email'])->first();
            if (!$instructor) {
                return response()->json(['message' => 'Instructor not found'], 404);
            }

            $session = CourseSession::create([
                'start_time' => $validated['session_start_time'],
                'end_time' => $validated['session_end_time'],
            ]);
            
            $course = Course::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'room' => $validated['room'],
                'section' => $validated['section'],
                'instructor_id' => $instructor->id,
                'session_id' => $session->id,
            ]);
    }
}
