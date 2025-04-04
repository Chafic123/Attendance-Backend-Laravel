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
            'Code' => 'required|string',
            'Room' => 'required|string',
            'credit' => 'required|integer',
            'Section' => 'required|string',
            'day_of_week' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'instructor_first_name' => 'required|string',
            'instructor_last_name' => 'required|string',
            'instructor_email' => [
                'required',
                'email',
                function ($value, $fail) {
                    if (User::where('email', $value)->orWhere('personal_email', $value)->exists()) {
                        $fail("Email already exists.");
                    }
                }
            ],
        ]);

        $user = User::where('email', $validated['instructor_email'])->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'No instructor record found for the provided email.'
            ], 422);
        }

        $instructor = Instructor::where('user_id', $user->id)->first();

        if (!$instructor) {
            return response()->json([
                'message' => 'No instructor record found for the provided email.'
            ], 422);
        }

        $existingCourseSection = Course::where('Code', $validated['Code'])
            ->where('Section', $validated['Section'])
            ->exists();

        if ($existingCourseSection) {
            return response()->json([
                'message' => 'A session with this course and section already exists.'
            ], 422);
        }

        $existingCourse = Course::where('Room', $validated['Room'])
            ->where('day_of_week', $validated['day_of_week'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhere(function ($q) use ($validated) {
                          $q->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                      });
            })
            ->exists();

        if ($existingCourse) {
            return response()->json([
                'message' => 'Room is already busy during this time slot.'
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
