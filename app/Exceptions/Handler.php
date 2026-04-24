<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json($e->errors(), 400);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse($e->getMessage(), $request->path(), 404, 'Not Found');
            }
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse($e->getMessage(), $request->path(), 403, 'Forbidden');
            }
        });

        $this->renderable(function (AlreadyExistsException $e, $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse($e->getMessage(), $request->path(), 409, 'Conflict');
            }
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse($e->getMessage(), $request->path(), 401, 'Unauthorized');
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse($e->getMessage(), $request->path(), 400, 'Bad Request');
            }
        });
    }

    private function errorResponse(string $message, string $path, int $status, string $error): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'path' => '/' . $path,
            'status' => $status,
            'error' => $error,
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }
}
