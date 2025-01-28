<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user(); 
            $token = $user->createToken('authToken')->accessToken; 

            if ($user->status === 'Admin') {
                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    'status' => 'Admin',
                ], 200); 
            } elseif ($user->status === 'Student') {
                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    'status' => 'Student',
                ], 200); 
            } elseif ($user->status === 'Instructor') {
                return response()->json([
                    'token' => $token,
                    'user' => $user,
                    'status' => 'Instructor',
                ], 200); 
            } else {
                return response()->json([
                    'error' => 'Your account is not active',
                ], 403); 
            }
        }
        return response()->json(['error' => 'Unauthorized'], 401); 
    }
}
