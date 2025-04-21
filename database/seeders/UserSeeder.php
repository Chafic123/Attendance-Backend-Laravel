<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\Instructor;
use App\Models\Student;

class UserSeeder extends Seeder
{
    public function run()
    {
        // // Create Admin
        // $admin = User::create([
        //     'first_name' => 'FYP',
        //     'last_name' => 'Team',
        //     'email' => 'teamFYP@admin.rhu.edu.lb',
        //     'password' => 'admin123',
        //     'status' => 'Admin',
        // ]);
        // Admin::create(['user_id' => $admin->id]);

        // // Create Instructor
        // $instructor = User::create([
        //     'first_name' => 'Roaa',
        //     'last_name' => 'Soloh',
        //     'email' => 'SolohRk@instructor.rhu.edu.lb',
        //     'password' => 'roaasoloh',
        //     'status' => 'Instructor',
        // ]);
        // Instructor::create([
        //     'user_id' => $instructor->id,
        //     'phone_number' => '81657588',
        //     'image' => 'default.png',
        //     'department_id' => 3,
        // ]);

        // Create Student
        $student = User::create([
            'first_name' => 'chafic  ',
            'last_name' => 'Achour',
            'email' => 'AchourCM@students.rhu.edu.lb',
            'password' => "123456",
            'status' => 'Student',
        ]);
        Student::create([
            'user_id' => $student->id,
            'phone_number' => '81657999',
            'major' => 'Electrical Engineering',
            'image' => 'default.png',
            'video' => 'default.mp4',
            'department_id' => 3,
        ]);
    }
}
