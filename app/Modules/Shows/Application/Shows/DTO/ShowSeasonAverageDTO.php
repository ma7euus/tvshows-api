<?php

namespace App\Modules\Shows\Application\Shows\DTO;

use App\Models\Show;

final class ShowSeasonAverageDTO
{
    /**
     * @param SeasonAverageDTO[] $averages
     */
    public function __construct(
        public readonly Show $show,
        public readonly array $averages,
    ) {}
}
