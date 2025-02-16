<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Attendance;
use App\Models\CourseSession;
use App\Services\CourseSessionGenerator;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CourseSessionController extends Controller
{
    protected $sessionGenerator;

    public function __construct(CourseSessionGenerator $sessionGenerator)
    {
        $this->sessionGenerator = $sessionGenerator;
    }

    public function generateSessionsForCourse($courseId)
    {
        $sessions = CourseSession::where('course_id', $courseId)
            ->orderBy('date') 
            ->get();

        // Fetch attendance records for each session
        $attendances = Attendance::whereIn('course_session_id', $sessions->pluck('id'))
            ->get();

        // Group the attendance records by session_id
        $attendanceGrouped = $attendances->groupBy('course_session_id');

        // Map the sessions and include the attendance status for each session
        $calendarData = $sessions->map(function ($session) use ($attendanceGrouped) {
            return [
                'session_id'   => $session->id,
                'session_date' => Carbon::parse($session->session_date)->format('Y-m-d'),
                'attendances'  => $attendanceGrouped->get($session->id, collect())->map(function ($attendance) {
                    return [
                        'student_id'   => $attendance->student_id,
                        'is_present'   => $attendance->is_present,
                        'attended_at'  => $attendance->attended_at ? Carbon::parse($attendance->attended_at)->format('Y-m-d H:i:s') : null,
                    ];
                }),
            ];
        });

        return response()->json($calendarData);
    }
}
