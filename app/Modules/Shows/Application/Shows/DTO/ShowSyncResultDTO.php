<?php

namespace App\Modules\Shows\Application\Shows\DTO;

use App\Models\Show;

final class ShowSyncResultDTO
{
    public function __construct(
        public readonly Show $show,
        public readonly bool $created,
    ) {}
}
