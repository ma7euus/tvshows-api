<?php

namespace App\Modules\Shared\Infrastructure\Security;

use App\Enums\Role;
use App\Models\User;
use App\Modules\Shared\Domain\Security\Contracts\RoleAuthorizerInterface;

final class RolePermissionService implements RoleAuthorizerInterface
{
    public function hasAnyRole(User $user, array $allowedRoles): bool
    {
        $userRole = Role::fromStr($user->role);
        /*if ($userRole === Role::ADMIN) {
            return true;
        }*/

        $normalizedAllowedRoles = array_map(
            static fn (string $role) => strtoupper(trim($role)),
            $allowedRoles,
        );

        return in_array($userRole->toStr(), $normalizedAllowedRoles, true);
    }
}
