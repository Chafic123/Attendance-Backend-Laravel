<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $students = Student::all(); 
        $attendances = Attendance::all(); 

        if ($students->isEmpty() || $attendances->isEmpty()) {
            echo "Please add students and attendance records to the database first.";
            return;
        }

        foreach ($students as $student) {
            foreach ($attendances as $attendance) {
                DB::table('attendance_requests')->insert([
                    'student_id' => $student->id,
                    'attendance_id' => $attendance->id,
                    'reason' => 'This is a sample reason for the attendance request.', 
                    'request_date' => now()->toDateString(), 
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo "Attendance requests have been seeded!";
    }
}
