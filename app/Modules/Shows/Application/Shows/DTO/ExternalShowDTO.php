<?php

namespace App\Modules\Shows\Application\Shows\DTO;

final class ExternalShowDTO
{
    /**
     * @param ExternalEpisodeDTO[] $episodes
     */
    public function __construct(
        public readonly int $integrationId,
        public readonly ?string $name,
        public readonly ?string $type,
        public readonly ?string $language,
        public readonly ?string $status,
        public readonly ?int $runtime,
        public readonly ?int $averageRuntime,
        public readonly ?string $officialSite,
        public readonly ?float $rating,
        public readonly ?string $summary,
        public readonly array $episodes = [],
    ) {}
}
