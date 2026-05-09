<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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
                return $this->errorResponse(
                    message: 'Validation failed.',
                    path: $request->path(),
                    status: 422,
                    error: 'Unprocessable Entity',
                    details: ['errors' => $e->errors()],
                );
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse(
                    $this->formatModelNotFoundMessage($e),
                    $request->path(),
                    404,
                    'Not Found',
                );
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

        $this->renderable(function (HttpExceptionInterface $e, $request) {
            if ($request->expectsJson()) {
                $status = $e->getStatusCode();

                return $this->errorResponse(
                    $e->getMessage() !== '' ? $e->getMessage() : (Response::$statusTexts[$status] ?? 'Error'),
                    $request->path(),
                    $status,
                    Response::$statusTexts[$status] ?? 'Error',
                );
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                return $this->errorResponse(
                    config('app.debug') ? $e->getMessage() : 'Internal Server Error',
                    $request->path(),
                    500,
                    'Internal Server Error',
                );
            }
        });
    }

    private function errorResponse(
        string $message,
        string $path,
        int $status,
        string $error,
        array $details = [],
    ): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'path' => '/' . ltrim($path, '/'),
            'status' => $status,
            'error' => $error,
            ...$details,
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }

    private function formatModelNotFoundMessage(ModelNotFoundException $exception): string
    {
        $model = $exception->getModel();

        if (!$model) {
            return 'Resource not found.';
        }

        return sprintf('%s not found.', class_basename($model));
    }
}
