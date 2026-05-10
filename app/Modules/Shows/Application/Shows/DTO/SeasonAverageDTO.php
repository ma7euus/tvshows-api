<?php

namespace App\Modules\Shows\Application\Shows\DTO;

final class SeasonAverageDTO
{
    public function __construct(
        public readonly int $season,
        public readonly float $averageRating,
    ) {}
}
