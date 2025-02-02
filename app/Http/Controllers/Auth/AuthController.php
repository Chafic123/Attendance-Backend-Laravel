<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',  
            'password' => 'required|string',
        ]);

        $identifier = $request->identifier;
        $password = $request->password;

        $student = Student::where('student_id', $identifier)->first();

        if ($student) {
            return $this->loginUser($student->user, $password, 'Student');
        } else {
            $user = User::where('email', $identifier)->first();
            if ($user) {
                if ($user->status === 'Admin') {
                    return $this->loginUser($user, $password, 'Admin');
                } elseif ($user->status === 'Instructor') {
                    return $this->loginUser($user, $password, 'Instructor');
                }
            }

            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }

    private function loginUser($user, $password, $role)
    {
        if ($role === 'Student') {
            
            if (Hash::check($password, $user->password)) {
               dd($user);
                $token = $user->createToken('StudentToken')->plainTextToken;
                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    'role' => 'Student',
                ], 200);
            }
        } else {
            if (Auth::attempt(['email' => $user->email, 'password' => $password])) {
                $token = $user->createToken($role . 'Token')->plainTextToken;
                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    'role' => $role,
                ], 200);
            }
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logout successful']);
    }
}
