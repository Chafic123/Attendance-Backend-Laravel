<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseSession;
use App\Models\Course;
use App\Models\Schedule;
use Carbon\Carbon;

class CourseSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course1 = Course::first(); 
        $schedule1 = Schedule::first();

        if (!$course1 || !$schedule1) {
            echo " No courses or schedules found. Seed them first!";
            return;
        }

        CourseSession::create([
            'course_id' => $course1->id,
            'schedule_id' => $schedule1->id,
            'start_time' => Carbon::createFromTime(9, 0, 0), 
            'end_time' => Carbon::createFromTime(10, 30, 0),
        ]);

    }
}
