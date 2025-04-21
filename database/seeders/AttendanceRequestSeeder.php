<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('attendance_requests')->insert([
            [
                'student_id' => 4, 
                'attendance_id' => 3, 
                'course_id' => 1, 
                'reason' => 'Missed class due to illness',
                'request_date' => Carbon::now(),
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'student_id' => 2,
                'attendance_id' => 4,
                'course_id' => 1,
                'reason' => 'Attended but marked absent',
                'request_date' => Carbon::now(),
                'status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
        ]);
    }
}
