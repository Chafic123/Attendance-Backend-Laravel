<?php

namespace App\Http\Controllers\Student;

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

        $coursesWithInstructor = $courses->map(function ($course) use ($student) {

            // Get attendance records for this course
            $attendanceRecords = \App\Models\Attendance::where('student_id', $student->id)
                ->whereHas('course_session', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })->get();

            $totalSessions = $attendanceRecords->count();
            $absentCount   = $attendanceRecords->where('is_present', false)->count();

            $deduction = $absentCount * 3.13;
            $attendancePercentage = max($deduction, 0);

            return [
                'course_name'           => $course->name,
                'course_code'           => $course->Code,
                'instructor_name'       => $course->instructors->first()
                    ? $course->instructors->first()->user->first_name . ' ' . $course->instructors->first()->user->last_name
                    : 'No instructor assigned',
                'attendance_percentage' => round($attendancePercentage, 2)
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


    public function getScheduleReportForLoggedInStudent()
    {
        $student = Auth::user()->student;
    
        if (!$student) {
            return response()->json([
                'error' => 'Student not logged in'
            ], 401);
        }
    
        $scheduleReport = $student->courses()
            ->with([
                'terms',
                'instructors.user:id,first_name,last_name', 
            ])
            ->get();
    
        $response = [
            'student' => [
                'student_id' => $student->student_id,
                'first_name' => $student->user->first_name,
                'last_name' => $student->user->last_name,
                'department' => $student->department->name ?? null,
                'email' => $student->user->email,
                'phone' => $student->phone_number,
                'major' => $student->major,
            ],
            'courses' => $scheduleReport->map(function ($course) {
                return [
                    'course_name' => $course->name,
                    'course_code' => $course->Code, 
                    'room_name' => $course->Room, 
                    'day_of_week' => str_split($course->day_of_week),
                    'section_name' => $course->Section,
                    'time_start' => $course->start_time,
                    'time_end' => $course->end_time,
                    'term' => $course->terms->first()->name ?? null,
                    'year' => $course->terms->first()->year ?? null,
                    'term_start_at' => $course->terms->first()->start_time,
                    'term_end_at' => $course->terms->first()->end_time,
                    'instructors' => $course->instructors->map(function ($instructor) {
                        return [
                            'first_name' => $instructor->user->first_name,
                            'last_name' => $instructor->user->last_name,
                        ];
                    }),
                ];
            }),
        ];
    
        return response()->json($response);
    }
    
}
