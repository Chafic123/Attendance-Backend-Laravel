<?php

namespace Database\Seeders;


\App\Models\Attendance::truncate();
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\CourseSession;

class AttendanceSeeder extends Seeder
{
    public function run()
    {

        $courseSessions = CourseSession::all();

        $students = Student::all();

        foreach ($courseSessions as $session) {
            foreach ($students as $student) {

                $isPresent = rand(1, 100) <= 70; 

                $attendantAt = Carbon::parse($session->date)
                    ->setTime(rand(8, 10), rand(0, 59)); 

                Attendance::create([
                    'course_session_id' => $session->id,
                    'student_id'        => $student->id,
                    'is_present'        => $isPresent, 
                    'attended_at'      => $attendantAt,
                ]);
            }
        }
    }
}
