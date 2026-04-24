<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     description="Payload para login de um usuário",
 *     required={"username", "password"},
 *     @OA\Property(property="username", type="string", description="Login para acesso"),
 *     @OA\Property(property="password", type="string", description="Senha do usuário")
 * )
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
        ];
    }
}
