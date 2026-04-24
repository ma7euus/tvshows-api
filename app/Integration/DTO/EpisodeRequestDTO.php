<?php

namespace App\Integration\DTO;

/**
 * DTO para representação de Episódios da API TVMaze.
 * Equivalente ao EpisodeRequestDTO.java do projeto Spring Boot.
 */
class EpisodeRequestDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?int $season = null,
        public readonly ?int $number = null,
        public readonly ?string $type = null,
        public readonly ?string $airdate = null,
        public readonly ?string $airtime = null,
        public readonly ?string $airstamp = null,
        public readonly ?int $runtime = null,
        public readonly ?RatingDTO $rating = null,
        public readonly ?string $summary = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            season: $data['season'] ?? null,
            number: $data['number'] ?? null,
            type: $data['type'] ?? null,
            airdate: $data['airdate'] ?? null,
            airtime: $data['airtime'] ?? null,
            airstamp: $data['airstamp'] ?? null,
            runtime: $data['runtime'] ?? null,
            rating: RatingDTO::fromArray($data['rating'] ?? null),
            summary: $data['summary'] ?? null,
        );
    }
}
