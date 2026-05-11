<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class EnsureEnabledApiUser
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('api')->user();

        if (!$user || !$user->enabled) {
            throw new AuthenticationException('User not found or disabled.');
        }

        return $next($request);
    }
}
