<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Auth", description="API de controle autenticações")
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login de um usuário",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(response=200, description="Login realizado com sucesso",
     *         @OA\JsonContent(@OA\Property(property="token", type="string"))
     *     ),
     *     @OA\Response(response=401, description="Credenciais inválidas")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ];

        $user = User::query()
            ->where('username', $credentials['username'])
            ->first();

        if (!$user || !$user->enabled) {
            throw new AuthenticationException('Bad credentials.');
        }

        if (!$token = auth('api')->attempt($credentials)) {
            throw new AuthenticationException('Bad credentials.');
        }

        return response()->json(['token' => $token]);
    }
}
