<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $instructors = Instructor::all(); 
        $students = Student::all(); 

        
        if ($instructors->isEmpty() || $students->isEmpty()) {
            echo "Please add instructors and students to the database first.";
            return;
        }

        
        foreach ($instructors as $instructor) {
            foreach ($students as $student) {
                // DB::table('notifications')->insert([
                //     'instructor_id' => $instructor->id,
                //     'student_id' => $student->id,
                //     'message' => 'This is a sample notification message.',
                //     'type' => 'Regular',
                //     'read_status' => false,
                //     'created_at' => now(),
                //     'updated_at' => now(),
                //     'course_id' => 1,
                // ]);
                DB::table('notifications')->insert([
                    'instructor_id' => $instructor->id,
                    'student_id' => $student->id,
                    'message' => "Please don't miss your class today",
                    'type' => 'Warning',
                    'read_status' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'course_id' => 1,
                ]);
            }
        }

        echo "Notifications have been seeded!";
    }
}
