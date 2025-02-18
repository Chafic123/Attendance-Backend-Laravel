<?php

namespace Database\Seeders;

// \App\Models\CourseSession::truncate();

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Term;
use App\Models\CourseSession;

class CourseSessionSeeder extends Seeder
{
    public function run()
    {
        //only term 2
        $term = Term::find(2);

        if (!$term) {
            $this->command->info('Spring term  not found.');
            return;
        }

        $startDate = Carbon::parse($term->start_time);
        $endDate   = Carbon::parse($term->end_time);

        $courses = $term->courses;

        foreach ($courses as $course) {
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                if ($this->matchesCourseDays($course->day_of_week, $currentDate)) {
                    CourseSession::firstOrCreate([
                        'course_id' => $course->id,
                        'date'      => $currentDate->format('Y-m-d'),
                    ], [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $currentDate->addDay();
            }
        }
    }

    private function matchesCourseDays(string $dayOfWeekString, Carbon $date): bool
    {
        $map = [
            0 => 'U', 
            1 => 'M', 
            2 => 'T', 
            3 => 'W', 
            4 => 'R', 
            5 => 'F', 
            6 => 'S', 
        ];

        $letter = $map[$date->dayOfWeek];

        return str_contains($dayOfWeekString, $letter);
    }
}
