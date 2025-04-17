<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Term;

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
            'instructor_id' => 'required|exists:instructors,id',
        ]);

        $instructor = Instructor::find($validated['instructor_id']);

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
            'credits' => $validated['credit'],
        ]);

        $course->instructors()->attach($instructor->id);
        
        $term = Term::whereDate('start_time', '<=', now())
            ->whereDate('end_time', '>=', now())
            ->first();

        if ($term) {
            $course->terms()->attach($term->id);

            $startDate = \Carbon\Carbon::parse($term->start_time);
            $endDate = \Carbon\Carbon::parse($term->end_time);

            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                if ($this->matchesCourseDays($course->day_of_week, $currentDate)) {
                    \App\Models\CourseSession::create([
                        'course_id' => $course->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $currentDate->addDay();
            }
        }
        
        return response()->json([
            'message' => 'Course added successfully with instructor assigned',
            'course' => $course,
            'instructor' => $instructor
        ], 201);
    }

    private function matchesCourseDays(string $dayOfWeekString, \Carbon\Carbon $date): bool
    {
        $map = [
            0 => 'U',  // Sunday
            1 => 'M',
            2 => 'T',
            3 => 'W',
            4 => 'R',
            5 => 'F',
            6 => 'S',  // Saturday
        ];

        $letter = $map[$date->dayOfWeek];

        return str_contains($dayOfWeekString, $letter);
    }
}