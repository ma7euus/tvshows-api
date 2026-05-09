<?php

namespace App\Modules\Shows\Domain\Shows\DTO;

final class SeasonAverageDTO
{
    public function __construct(
        public readonly int $season,
        public readonly float $averageRating,
    ) {}
}
