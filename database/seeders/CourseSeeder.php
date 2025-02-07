<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example seed data
        Course::create([
            'name' => 'Introduction to Programming',
            'Room' => 'H102',
            'Section' => '1',
        ]);
        Course::create([
            'name' => 'Introduction to Programming',
            'Room' => 'H104',
            'Section' => '2',
        ]);

        Course::create([
            'name' => 'Data Structures',
            'Room' => 'B202',
            'Section' => '1',
        ]);

        Course::create([
            'name' => 'Database Systems',
            'Room' => 'I228',
            'Section' => '1',
        ]);

        Course::create([
            'name' => 'Operating Systems',
            'Room' => 'H104',
            'Section' => '1',
        ]);
    }
}
