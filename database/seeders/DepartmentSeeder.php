<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        Department::create(['name' => 'Science']);
        Department::create(['name' => 'Engineering']);
        Department::create(['name' => 'Business']);
    }
}
