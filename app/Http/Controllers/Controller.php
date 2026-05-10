<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="API - TV Shows",
 *     description="API REST com autenticação JWT, gestão de usuários e sincronização de shows via TVMaze.",
 *     version="1.0.0"
 * )
 * @OA\Server(
 *     url="http://localhost:9012",
 *     description="Ambiente local via Docker"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * @OA\Schema(
 *     schema="ApiError",
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="path", type="string"),
 *     @OA\Property(property="status", type="integer"),
 *     @OA\Property(property="error", type="string"),
 *     @OA\Property(property="timestamp", type="string", format="date-time")
 * )
 */
abstract class Controller
{
    //
}
