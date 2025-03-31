<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Department;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\StudentCredentialsMail;
use App\Services\StudentEmailService;
use App\Services\PasswordService;

class AddStudentController extends Controller
{
    public function addStudent(Request $request)
    {
        $validated = $this->validateRequest($request);

        return DB::transaction(function () use ($validated) {
            $department = Department::findOrFail($validated['department_id']);

            $email = StudentEmailService::generateEmail(
                $validated['first_name'],
                $validated['last_name'],
                $validated['department_id']
            );

            $plainPassword = PasswordService::generateTemporaryPassword();
            $hashedPassword = Hash::make($plainPassword);

            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $email,
                'password' => $hashedPassword,
                'status' => 'Student',
            ]);

            Student::create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'address' => $validated['address'],
                'phone_number' => $validated['phone_number'],
                'personal_email' => $validated['personal_email'],
                'major' => $validated['major'],
                'processed_video' => false,
            ]);

            $this->sendWelcomeEmail(
                $validated['first_name'],
                $email,
                $validated['personal_email'],
                $plainPassword,
                $validated['major'],
                $department->name
            );

            return response()->json([
                'message' => 'Student added successfully!',
                'email' => $email,
                'personal_email' => $validated['personal_email'],
                'temporary_password' => $plainPassword,
                'department' => $department->name,
                'major' => $validated['major']
            ], 201);
        });
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'major' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'unique:students,phone_number',
            ],
            'personal_email' => [
                'required',
                'email:rfc,dns',
                'unique:students,personal_email',
                'unique:users,email',
                'max:255'
            ],
            'department_id' => 'required|exists:departments,id'
        ]);
    }

    protected function sendWelcomeEmail(
        string $firstName,
        string $studentEmail,
        string $personalEmail,
        string $password,
        string $major,
        string $departmentName
    ): void {
        try {
            Mail::to($personalEmail)
                ->send(new StudentCredentialsMail(
                    $firstName,
                    $studentEmail,
                    $personalEmail,
                    $password,
                    $major,
                    $departmentName
                ));
            Log::info("Welcome email sent to {$personalEmail}");
        } catch (\Exception $e) {
            Log::error('Failed to send student credentials email: ' . $e->getMessage());
        }
    }
}