<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Department;
use App\Services\InstructorEmailService;
use App\Services\PasswordService;
use App\Models\User;
use App\Models\Instructor;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Mail\Mailable;
use App\Mail\InstructorCredentialsMail; 

class AddInstructorController extends Controller
{
    public function addInstructor(Request $request)
    {
        $validated = $this->validateRequest($request);

        return DB::transaction(function () use ($validated) {
            $department = Department::findOrFail($validated['department_id']);

            $email = InstructorEmailService::generateEmail(
                $validated['first_name'],
                $validated['last_name'],
            );

            $plainPassword = PasswordService::generateTemporaryPassword();
            $hashedPassword = $plainPassword;

            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $email,
                'personal_email' => $validated['personal_email'],
                'password' => $hashedPassword,
                'status' => 'Instructor',
            ]);

            Instructor::create([
                'user_id' => $user->id,
                'department_id' => $department->id,
                'phone_number' => $validated['phone_number'],
            ]);

            $this->sendWelcomeEmail(
                $validated['first_name'],
                $email,
                $validated['personal_email'],
                $plainPassword,
                $department->name
            );

            return response()->json([
                'message' => 'Instructor added successfully!',
                'email' => $email,
                'personal_email' => $validated['personal_email'],
                'temporary_password' => $plainPassword,
                'department' => $department->name,
            ], 201);
        });
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                'unique:instructors,phone_number',
            ],
            'personal_email' => [
                'required',
                'email:rfc,dns',
                'unique:users,personal_email',
                'unique:users,email',
                'max:255'
            ],
            'department_id' => 'required|exists:departments,id'
        ]);
    }

    protected function sendWelcomeEmail(
        string $firstName,
        string $instructorEmail,
        string $personalEmail,
        string $password,
        string $departmentName
    ): void {
        try {
            Mail::to($personalEmail)
                ->send(new InstructorCredentialsMail(
                    $firstName,
                    $instructorEmail,
                    $personalEmail,
                    $password,
                    $departmentName,
                    // route('login'),
                    // route('privacy'),
                    // route('support')
                ));
            Log::info("Welcome email sent to {$personalEmail}");
        } catch (\Exception $e) {
            Log::error('Failed to send student credentials email: ' . $e->getMessage());
        }
    }
}
