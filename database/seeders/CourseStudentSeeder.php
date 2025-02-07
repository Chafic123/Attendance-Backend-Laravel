<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();
        $students = Student::all();

        if ($courses->isEmpty() || $students->isEmpty()) {
            echo "Please add courses and students to the database first.";
            return;
        }

        foreach ($courses as $course) {
            foreach ($students as $student) {
                DB::table('course_student')->insert([
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                    'enrollment-date' => now(), 
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo "Course student enrollments have been seeded!";
    }
}
