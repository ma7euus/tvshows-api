<?php

namespace App\Modules\Shows\Infrastructure\Integration\Exceptions;

final class TvMazeRateLimitException extends TransientTvMazeException
{
    public function __construct(string $message = 'TVMaze rate limit exceeded.', array $headers = [])
    {
        parent::__construct(429, $message, null, $headers);
    }
}
