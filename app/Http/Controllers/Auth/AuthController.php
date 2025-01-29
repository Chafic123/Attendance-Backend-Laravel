<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Student;
use App\Models\Instructor;
use App\Models\Admin;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'student_id' => 'required_without:user_id|string',
            'user_id' => 'required_without:student_id|string',
            'password' => 'required|string|min:6',
            'email' => 'required_without:student_id|email', 
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($request->has('student_id')) {
            $student = Student::where('student_id', $request->student_id)->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }

            if (Auth::attempt(['user_id' => $student->user_id, 'password' => $request->password])) {
                $user = Auth::user();
                $token = $user->createToken('studentToken')->accessToken;

                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    'status' => 'Student',
                ], 200);
            }
        }

        if ($request->has('email') && !$request->has('student_id')) {
            $admin = Admin::where('email', $request->email)->first(); 

            if ($admin && Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $token = $user->createToken('adminToken')->accessToken;

                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    'status' => 'Admin',
                ], 200);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        if ($request->has('email') && !$request->has('student_id')) {
            $instructor = Instructor::where('email', $request->email)->first();

            if (!$instructor) {
                return response()->json(['error' => 'Instructor not found'], 404);
            }

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $token = $user->createToken('instructorToken')->accessToken;

                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    'status' => 'Instructor',
                ], 200);
            }
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
