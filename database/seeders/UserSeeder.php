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
        // Create Admin
        $admin = User::create([
            'first_name' => 'Achour',
            'last_name' => 'CM',
            'email' => 'AchourCM@admin.rhu.edu.lb',
            'password' => '123456',
            'status' => 'Admin',
        ]);
        Admin::create(['user_id' => $admin->id]);

        // // Create Instructor
        $instructor = User::create([
            'first_name' => 'Roaa',
            'last_name' => 'Soloh',
            'email' => 'SolohRk@instructor.rhu.edu.lb',
            'password' => '123456',
            'status' => 'Instructor',
        ]);
        Instructor::create([
            'user_id' => $instructor->id,
            'username' => 'Roaa',
            'phone_number' => '81657588',
            'image' => 'default.png',
            'department_id' => 3,
        ]);

        // Create Student
        $student = User::create([
            'first_name' => 'Osama',
            'last_name' => 'Aawad',
            'email' => 'AawadOs@students.rhu.edu.lb',
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

        echo "Student created";
    }
}
