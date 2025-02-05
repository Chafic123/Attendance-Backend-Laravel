<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
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

    $user = null;

    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $user = User::where('email', $identifier)->first();
    } else {
        $student = Student::where('student_id', $identifier)->first();
        if ($student) {
            $user = $student->user; 
        }
}

    if ($user && Hash::check($password, hashedValue: $user->password)) {
        Auth::login($user); 

        return response()->json([
            'message' => 'Login successful!',
            'user' => $user->status,
        ]);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
}

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['status' => 'success', 'message' => 'Logged out']);
    }
}
