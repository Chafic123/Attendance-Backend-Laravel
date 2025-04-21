<?php

namespace App\Services;

use App\Models\Course;

use App\Models\Term;
use App\Models\CourseSession;
use Carbon\Carbon;
use App\Models\Attendance;

class CourseSessionGenerator
{
    public function generate(Course $course)
    {
        $term = $course->terms->first();
        $startDate = Carbon::parse($term->start_time);
        $endDate = Carbon::parse($term->end_time);
    
        $daysLetters = str_split($course->days_of_week);
        $dayMapping = [
            'M' => 1, 'T' => 2, 'W' => 3, 'R' => 4, 'F' => 5
        ];
    
        $validDays = array_intersect_key($dayMapping, array_flip($daysLetters));
    
        $sessionsToInsert = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if (in_array($date->dayOfWeekIso, $validDays) && !$date->isWeekend()) {
                $sessionsToInsert[] = [
                    'course_id'    => $course->id,
                    'session_date' => $date->toDateString(),  
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
        }
    
        if (!empty($sessionsToInsert)) {
            CourseSession::insert($sessionsToInsert);
        }


        if (!empty($sessionsToInsert)) {

            $firstSession = $sessionsToInsert[0]; 

            $student = $course->students->first(); 

            if ($student) {
                Attendance::create([
                    'course_session_id' => $firstSession['id'],  
                    'student_id'        => $student->id,
                    'is_present'        => false,  
                    'attended_at'       => now(),  
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        }
    }
    
}
