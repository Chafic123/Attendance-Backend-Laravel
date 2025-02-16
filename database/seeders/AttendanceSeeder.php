<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\CourseSession;
use App\Models\Student;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $sessions = CourseSession::all();
        $students = Student::all();

        foreach ($sessions as $session) {
            foreach ($students as $student) {
                Attendance::create([
                    'course_session_id' => $session->id,
                    'student_id' => $student->id,
                    'Attended_at'  => $session->date . ' ' . $session->course->start_time,
                    'is_present' => rand(0, 1),
                ]);                    
            }
        }
    }
}
