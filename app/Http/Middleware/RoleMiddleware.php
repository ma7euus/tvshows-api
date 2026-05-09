<?php

namespace App\Http\Middleware;

use App\Modules\Shared\Domain\Security\Contracts\RoleAuthorizerInterface;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function __construct(
        private readonly RoleAuthorizerInterface $roleAuthorizer,
    ) {}

    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = auth('api')->user();

        if (!$user) {
            throw new AuthenticationException('Unauthorized.');
        }

        if ($this->roleAuthorizer->hasAnyRole($user, $roles)) {
            return $next($request);
        }
        throw new AuthorizationException('Access denied.');
    }
}
