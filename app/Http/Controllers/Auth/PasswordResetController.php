<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\PasswordService;
use App\Mail\PasswordResetMail;

class PasswordResetController extends Controller
{
    public function resetPassword(Request $request)
    {
        $request->validate([
            'personal_email' => 'required|email|exists:users,personal_email'
        ]);

        $user = User::where('personal_email', $request->personal_email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found!'], 404);
        }

        $newPassword = PasswordService::generateTemporaryPassword();
        $hashedPassword = Hash::make($newPassword);

        $user->update(['password' => $hashedPassword]);

        try {
            Mail::to($user->personal_email)->send(new PasswordResetMail($user->first_name, $newPassword));
            Log::info("Password reset email sent to: {$user->personal_email}");

            return response()->json(['message' => 'New password sent to your email.'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send email. Try again later.'], 500);
        }
    }
}
