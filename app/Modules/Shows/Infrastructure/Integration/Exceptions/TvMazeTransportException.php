<?php

namespace App\Modules\Shows\Infrastructure\Integration\Exceptions;

use Throwable;

final class TvMazeTransportException extends TransientTvMazeException
{
    public function __construct(string $message = 'TVMaze integration is unavailable.', ?Throwable $previous = null)
    {
        parent::__construct(502, $message, $previous);
    }
}
