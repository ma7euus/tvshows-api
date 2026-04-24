<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user || !$user->enabled) {
                return response()->json([
                    'message' => 'User not found or disabled',
                    'status' => 401,
                    'error' => 'Unauthorized',
                ], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token expired',
                'status' => 401,
                'error' => 'Unauthorized',
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'message' => 'Token invalid',
                'status' => 401,
                'error' => 'Unauthorized',
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token not provided',
                'status' => 401,
                'error' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
