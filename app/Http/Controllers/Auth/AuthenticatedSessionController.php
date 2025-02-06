<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $identifier = $request->input('identifier');
        $password = $request->input('password');

        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $identifier)->first()
            : optional(Student::where('student_id', $identifier)->first())->user;

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken("{$user->first_name}'s Token")->plainTextToken;

        return response()->json([
            'message' => 'Login successful!',
            'status' => $user->status,
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ])
            ->cookie('access_token', $token, 240, null, null, true, true);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['status' => 'success', 'message' => 'Logged out']);
        }

        return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }
}
