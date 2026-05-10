<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user || !$user->enabled) {
                throw new AuthenticationException('User not found or disabled.');
            }
        } catch (TokenExpiredException $e) {
            throw new AuthenticationException('Token expired.');
        } catch (TokenInvalidException $e) {
            throw new AuthenticationException('Token invalid.');
        } catch (JWTException $e) {
            throw new AuthenticationException('Token not provided');
        }

        return $next($request);
    }
}
