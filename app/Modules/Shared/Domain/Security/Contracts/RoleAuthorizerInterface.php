<?php

namespace App\Modules\Shared\Domain\Security\Contracts;

use App\Models\User;

interface RoleAuthorizerInterface
{
    /**
     * @param string[] $allowedRoles
     */
    public function hasAnyRole(User $user, array $allowedRoles): bool;
}
