<?php

namespace App\Enums;

enum TvMazeImportStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case COMPLETE = 'complete';
    case ERROR = 'error';
}
