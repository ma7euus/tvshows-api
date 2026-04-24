<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="CManager API",
 *     description="JWT-based authentication + Users CRUD + TV Shows sync",
 *     version="v1"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    //
}
