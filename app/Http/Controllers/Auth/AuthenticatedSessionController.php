<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:255',
            'password' => 'required|string|',
            'remember_me' => 'boolean',
        ]);

        $authResult = $this->authService->authenticateUser(
            $request->input('identifier'),
            $request->input('password'),
            $request->input('remember_me', false)
        );

        if (!$authResult) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $cookieExpiration = $request->input('remember_me', false) ? 10080 : 120;

        return response()->json([
            'message' => 'Login successful!',
            'status' => $authResult['user']->status,
            'user' => $authResult['user']->makeHidden(['password', 'remember_token']),
            'access_token' => $authResult['token'],
            'token_type' => $authResult['token_type'],
            'expires_at' => $authResult['expires_at'],
        ])->cookie(
            'access_token',
            $authResult['token'],
            $cookieExpiration,
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        );
    }

    public function destroy()
    {
        $user = Auth::user();

        if (!$user instanceof \App\Models\User) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $user->update(['remember_token' => null]);

        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        return response()->json(['status' => 'success', 'message' => 'Logged out successfully'])
            ->cookie('access_token', '', -1);
    }
}
