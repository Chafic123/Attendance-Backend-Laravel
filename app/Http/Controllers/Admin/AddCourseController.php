<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\User;

class AddCourseController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'Code' => 'required|string|unique:courses,Code,NULL,id,Section,' . $request->input('Section'),
            'Room' => 'required|string',
            'Section' => 'required|string',
            'day_of_week' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'instructor_first_name' => 'required|string',
            'instructor_last_name' => 'required|string',
            'instructor_email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['instructor_email'])->first();

        if (!$user || $user->first_name !== $validated['instructor_first_name'] || $user->last_name !== $validated['instructor_last_name']) {
            return response()->json([
                'message' => 'Instructor name and email do not match our records.'
            ], 422);
        }

        $instructor = Instructor::where('user_id', $user->id)->first();

        if (!$instructor) {
            return response()->json([
                'message' => 'No instructor record found for the provided email.'
            ], 422);
        }

        $course = Course::create([
            'name' => $validated['name'],
            'Code' => $validated['Code'],
            'Room' => $validated['Room'],
            'Section' => $validated['Section'],
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        $course->instructors()->attach($instructor->id);

        return response()->json([
            'message' => 'Course added successfully with instructor assigned',
            'course' => $course,
            'instructor' => $user
        ], 201);
    }
}
