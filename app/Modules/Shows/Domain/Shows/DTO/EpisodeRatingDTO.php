<?php

namespace App\Modules\Shows\Domain\Shows\DTO;

final class EpisodeRatingDTO
{
    public function __construct(
        public readonly ?int $season,
        public readonly ?float $rating,
    ) {}
}
