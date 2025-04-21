<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthService
{
    public function authenticateUser($identifier, $password, $rememberMe)
    {
        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? User::where('email', $identifier)->first()
            : optional(Student::where('student_id', $identifier)->first())->user;
    
        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }
    
        config(['session.lifetime' => $rememberMe ? 10080 : 120]); // 7 days or 2 hours
    
        $tokenExpiration = $rememberMe ? Carbon::now()->addDays(7) : Carbon::now()->addHours(2);
    
        $token = $user->createToken("{$user->first_name}'s Token")->plainTextToken;
    
        // expiration 
        $user->tokens()->latest()->first()->update([
            'expires_at' => $tokenExpiration,
        ]);
    
        if ($rememberMe) {
            $user->remember_token = Str::random(60);
        } else {
            $user->remember_token = null; 
        }
        
        $user->save(); 
    
        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_at' => $tokenExpiration
        ];
    }

    public function logoutUser($user)
    {
        if ($user) {
            $user->remember_token = null;
            $user->save();
            $user->tokens()->delete(); 
        }
        return ['status' => 'success', 'message' => 'Logged out successfully'];
    }
}
