<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;

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
            'password' => 'required|string|min:8',
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

        return response()->json([
            'message' => 'Login successful!',
            'status' => $authResult['user']->status,
            'user' => $authResult['user']->makeHidden(['password', 'remember_token']),
            'access_token' => $authResult['token'],
            'token_type' => $authResult['token_type'],
        ])->cookie(
            'access_token',
            $authResult['token'],
            $request->input('remember_me', false) ? 10080 : 120,
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        );
    }

    public function destroy(Request $request)
    {
        $response = $this->authService->logoutUser($request->user());

        return response()->json($response, $response['status'] === 'success' ? 200 : 401);
    }
}
