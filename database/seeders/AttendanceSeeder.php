<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\CourseSession;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        Attendance::truncate();

        $courseSessions = CourseSession::all();
        $students = Student::all();
        $today = Carbon::today();

        foreach ($courseSessions as $session) {
            foreach ($students as $student) {

                $sessionDate = Carbon::parse($session->date);

                if ($sessionDate->lte($today)) {
                    // Mark as present with a random realistic time
                    $isPresent = true;
                    $attendedAt = $sessionDate->copy()->setTime(rand(8, 10), rand(0, 59));
                } else {
                    // Future session: not yet attended
                    $isPresent = null;
                    $attendedAt = null;
                }

                Attendance::create([
                    'course_session_id' => $session->id,
                    'student_id'        => $student->id,
                    'is_present'        => $isPresent,
                    'attended_at'       => $attendedAt,
                ]);
            }
        }
    }
}
