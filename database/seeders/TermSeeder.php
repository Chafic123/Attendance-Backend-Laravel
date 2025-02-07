<?php

namespace Database\Seeders;

use App\Models\Term;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TermSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Term::create([
            'name' => 'Fall',
            'start_time' => '2024-09-01',
            'end_time' => '2024-12-31',
            'year' => '2024',
        ]);
        Term::create([
            'name' => 'Spring',
            'start_time' => '2025-01-01',
            'end_time' => '2025-05-31',
            'year' => '2025',
        ]);
        Term::create([
            'name' => 'Summer',
            'start_time' => '2025-06-20',
            'end_time' => '2025-08-15',
            'year' => '2025',
        ]);
        
    }
}
