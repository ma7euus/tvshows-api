<?php

namespace App\Modules\Shows\Infrastructure\Integration\Exceptions;

final class TvMazeServiceUnavailableException extends TransientTvMazeException
{
    public function __construct(string $message = 'TVMaze service is unavailable.', array $headers = [])
    {
        parent::__construct(503, $message, null, $headers);
    }
}
