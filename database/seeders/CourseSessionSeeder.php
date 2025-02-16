<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Services\CourseSessionGenerator;

class CourseSessionSeeder extends Seeder
{
    // Inject CourseSessionGenerator as a dependency
    protected $sessionGenerator;

    public function __construct(CourseSessionGenerator $sessionGenerator)
    {
        $this->sessionGenerator = $sessionGenerator;
    }

    public function run()
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            $this->sessionGenerator->generate($course); 
        }
    }
}
