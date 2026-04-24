<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UserCreateRequest",
 *     description="Payload para criação de um usuário",
 *     required={"username", "password", "role", "enabled"},
 *     @OA\Property(property="username", type="string", minLength=3, maxLength=100, description="Login para acesso"),
 *     @OA\Property(property="password", type="string", minLength=6, maxLength=200, description="Senha para acesso"),
 *     @OA\Property(property="role", type="string", enum={"ADMIN", "USER"}, description="Permissão"),
 *     @OA\Property(property="enabled", type="boolean", description="Habilitado true ou false")
 * )
 */
class UserCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|min:3|max:100',
            'password' => 'required|string|min:6|max:200',
            'role' => ['required', Rule::enum(Role::class)],
            'enabled' => 'required|boolean',
        ];
    }
}
