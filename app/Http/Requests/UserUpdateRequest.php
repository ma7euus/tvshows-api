<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UserUpdateRequest",
 *     description="Payload para atualização de um usuário",
 *     @OA\Property(property="username", type="string", minLength=3, maxLength=100, description="Login para acesso"),
 *     @OA\Property(property="password", type="string", minLength=6, maxLength=200, description="Senha para acesso"),
 *     @OA\Property(property="role", type="string", enum={"ADMIN", "USER"}, description="Permissões"),
 *     @OA\Property(property="enabled", type="boolean", description="Habilitado true ou false")
 * )
 */
class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'sometimes|string|min:3|max:100',
            'password' => 'sometimes|string|min:6|max:200',
            'role' => ['sometimes', Rule::enum(Role::class)],
            'enabled' => 'sometimes|boolean',
        ];
    }
}
