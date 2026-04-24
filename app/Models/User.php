<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasUuids;

    protected $table = 'users';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'username',
        'password',
        'role',
        'enabled',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => 'ROLE_' . $this->role,
            'username' => $this->username,
        ];
    }

    // Auth: Laravel usa "email" por padrão, sobrescrevemos para "username"
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }
}
