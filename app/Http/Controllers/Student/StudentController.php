<?php

namespace App\Http\Controllers\Student;

use App\Models\Student;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class StudentController extends Controller
{
    public function getCoursesForLoggedInStudent()
    {
        $user = Auth::user();

        $student = $user->student;

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $courses = $student->courses()->with('instructors.user')->get();

        $coursesWithInstructor = $courses->map(function ($course) {
            return [
                'course_name'     => $course->name,
                'course_code'     => $course->Code,
                'instructor_name' => $course->instructors->first() ? $course->instructors->first()->user->first_name . ' ' . $course->instructors->first()->user->last_name : 'No instructor assigned',
            ];
        });

        return response()->json($coursesWithInstructor);
    }

    public function getNotificationsForLoggedInStudent()
    {
        $student = Auth::user()->student;  
        if (!$student) {
            return response()->json([
                'error' => 'Student not logged in'
            ], 401);
        }

        $notifications = Notification::where('student_id', $student->id)
            ->with('instructor.user')  
            ->get();
        $notificationsData = $notifications->map(function ($notification) {
            return [
                'message'          => $notification->message,
                'type'             => $notification->type,
                'instructor_name'  => $notification->instructor ? 
                                    $notification->instructor->user->first_name . ' ' . $notification->instructor->user->last_name : 'No instructor assigned',
            ];
        });

        return response()->json($notificationsData);
    }
}
