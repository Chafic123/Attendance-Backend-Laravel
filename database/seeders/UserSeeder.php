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
            'email' => 'AchourCM1@admin.rhu.edu.lb',
            'password' => 'shafi-2458',
            'status' => 'Admin',
        ]);
        Admin::create(['user_id' => $admin->id]);

        // Create Instructor
        $instructor = User::create([
            'first_name' => 'Achour',
            'last_name' => 'CM',
            'email' => 'AchourCM@instructor.rhu.edu.lb',
            'password' => 'shafi-2457',
            'status' => 'Instructor',
        ]);
        Instructor::create([
            'user_id' => $instructor->id,
            'username' => 'AchourInstructor',
            'phone_number' => '81657588',
            'image' => 'default.png',
            'department_id' => 2,
        ]);

        // // Create Student
        $student = User::create([
            'first_name' => 'jawad',
            'last_name' => 'CM',
            'email' => 'JawadCM@students.rhu.edu.lb',
            'password' => "shafi-2456",
            'status' => 'Student',
        ]);
        Student::create([
            'user_id' => $student->id,
            'phone_number' => '81657588',
            'major' => 'Computer Science',
            'image' => 'default.png',
            'video' => 'default.mp4',
            'department_id' => 1,
        ]);

        echo "Student created";
    }
}
