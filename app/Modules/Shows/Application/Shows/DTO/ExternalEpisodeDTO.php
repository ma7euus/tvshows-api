<?php

namespace App\Modules\Shows\Application\Shows\DTO;

final class ExternalEpisodeDTO
{
    public function __construct(
        public readonly int $integrationId,
        public readonly ?string $name,
        public readonly ?int $season,
        public readonly ?int $number,
        public readonly ?string $type,
        public readonly ?string $airdate,
        public readonly ?string $airtime,
        public readonly ?string $airstamp,
        public readonly ?int $runtime,
        public readonly ?float $rating,
        public readonly ?string $summary,
    ) {}
}
