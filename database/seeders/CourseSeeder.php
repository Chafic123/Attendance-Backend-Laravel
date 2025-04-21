<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Course::create([
            'name' => 'Introduction to Programming',
            'Code' => 'COSC214',
            'Room' => 'H102',
            'Section' => '1',
            'day_of_week' => 'MW',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'credits' => 3,
        ]);
        Course::create([
            'name' => 'Introduction to Programming',
            'Code' => 'COSC214',
            'Room' => 'H104',
            'Section' => '2',
            'day_of_week' => 'MW',
            'start_time' => '02:00:00',
            'end_time' => '4:00:00',
            'credits' => 3,
        ]);
        Course::create([
            'name' => 'Web Development',
            'Code' => 'COSC333',
            'Room' => 'I228',
            'Section' => '1',
            'day_of_week' => 'TR',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'credits' => 3,
        ]);

        Course::create([
            'name' => 'Advanced Web Development',
            'Code' => 'COSC424',
            'Room' => 'I204',
            'Section' => '1',
            'day_of_week' => 'TR',
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'credits' => 3,
        ]);
    }
}
