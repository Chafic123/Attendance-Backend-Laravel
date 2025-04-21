<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course; 
use App\Models\Term;   
use Illuminate\Support\Facades\DB;

class CourseTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();
        $terms = Term::all();

        if ($courses->isEmpty() || $terms->isEmpty()) {
            echo "No courses or terms found. Seed them first!";
            return;
        }

        foreach ($courses as $course) {
            $course->terms()->attach(
                $terms->random(rand(1, 2))->pluck('id')->toArray() 
            );
        }
    }
}
