<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Department;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csDepartment = Department::firstOrCreate([
            'name' => 'Business Department',
        ]);

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Achour',
            'email' => 'AchourCM@admin.rhu.edu.lb',
            'password' => bcrypt('123456'),
            'status' => 'Admin', 
        ]);

        Admin::create([
            'user_id' => $admin->id,
        ]);

        // $student = User::create([
        //     'first_name' => 'Student',
        //     'last_name' => 'Achour',
        //     'email' => 'AchourCM1@student.rhu.edu.lb',
        //     'password' => bcrypt('123456'),
        //     'status' => 'Student',  
        // ]);

        // Student::create([
        //     'user_id' => $student->id,
        //     'department_id' => $csDepartment->id, 
        //     'phone_number' => '81657587',
        //     'major' => 'Software Engineering',
        //     'address' => 'Beirut',
        //     'image' => 'chafic.jpg',
        //     'video' => 'chafic.mp4',
        // ]);

    //     $instructor = User::create([
    //         'first_name' => 'Instructor',
    //         'last_name' => 'Achour',
    //         'email' => 'AchourCM@instructor.rhu.edu.lb',
    //         'password' => bcrypt('123456'),
    //         'status' => 'Instructor', 
    //     ]);

    //     Instructor::create([
    //         'user_id' => $instructor->id,
    //         'username' => 'Chafic123',
    //         'phone_number' => '81657586',
    //         'image' => 'chafic.jpg',
    //         'department_id' => $csDepartment->id, 
    //     ]);
    }
}
