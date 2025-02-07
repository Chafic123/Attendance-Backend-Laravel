<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseInstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $courses = Course::all();
        $instructors = Instructor::all();

        if ($courses->isEmpty() || $instructors->isEmpty()) {
            echo "Please add courses and instructors to the database first.";
            return;
        }


        foreach ($courses as $course) {
            foreach ($instructors as $instructor) {
                DB::table('course_instructor')->insert([
                    'course_id' => $course->id,
                    'instructor_id' => $instructor->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo "Course-Instructor relationships have been seeded!";
    }
}
