<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('access_token'); 

        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Find the token in database
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || !$accessToken->tokenable) {
            return response()->json(['message' => 'Invalid or expired token'], 401);
        }

        // Authenticate the user
        $request->setUserResolver(fn() => $accessToken->tokenable);

        return $next($request);
    }
}
