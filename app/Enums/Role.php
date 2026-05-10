<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'ADMIN';
    case USER = 'USER';

    public static function fromStr(string $role): self
    {
        if ($roleEnum = Role::tryFrom(strtoupper($role))) {
            return $roleEnum;
        }
        throw new \InvalidArgumentException("Role '$role' is not valid");
    }

    public function toStr(): string {
        return $this->value;
    }

    public function middleware(): string {
        return 'role:' . $this->toStr();
    }
}
