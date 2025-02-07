<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance; // Import the Attendance model
use App\Models\CourseSession;
use App\Models\Student;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courseSessions = CourseSession::all();
        $students = Student::all();

        if ($courseSessions->isEmpty() || $students->isEmpty()) {
            echo " No course sessions or students found. Seed them first!";
            return;
        }

        foreach ($courseSessions as $courseSession) {
            foreach ($students as $student) {
                $isPresent = rand(0, 1) == 1;

                Attendance::create([
                    'course_session_id' => $courseSession->id,
                    'student_id' => $student->id,
                    'is_present' => $isPresent,
                ]);
            }
        }
    }
}
