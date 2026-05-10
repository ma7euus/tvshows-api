<?php

namespace App\Modules\Shows\Infrastructure\Integration\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

abstract class TransientTvMazeException extends HttpException
{
    public function __construct(
        int $statusCode,
        string $message,
        ?Throwable $previous = null,
        array $headers = [],
    ) {
        parent::__construct($statusCode, $message, $previous, $headers);
    }
}
