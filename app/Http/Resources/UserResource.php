<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="UserDTO",
 *     description="Response da entidade de usuario",
 *     @OA\Property(property="id", type="string", description="Id do usuário"),
 *     @OA\Property(property="username", type="string", description="Username do usuário"),
 *     @OA\Property(property="role", type="string", enum={"ADMIN", "USER"}, description="Role do usuário"),
 *     @OA\Property(property="enabled", type="boolean", description="Usuário ativo")
 * )
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'role' => $this->role,
            'enabled' => $this->enabled,
        ];
    }
}
