<?php

namespace App\Modules\Shows\Infrastructure\Integration\DTO;

/**
 * DTO para representação de Shows da API TVMaze.
 * Equivalente ao ShowsRequestDTO.java do projeto Spring Boot.
 */
class ShowsRequestDTO
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $type = null,
        public readonly ?string $language = null,
        public readonly ?string $status = null,
        public readonly ?int $runtime = null,
        public readonly ?int $averageRuntime = null,
        public readonly ?string $officialSite = null,
        public readonly ?RatingDTO $rating = null,
        public readonly ?string $summary = null,
        /** @var EpisodeRequestDTO[] */
        public readonly array $episodes = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $episodes = [];
        if (isset($data['_embedded']['episodes'])) {
            foreach ($data['_embedded']['episodes'] as $ep) {
                $episodes[] = EpisodeRequestDTO::fromArray($ep);
            }
        }

        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            type: $data['type'] ?? null,
            language: $data['language'] ?? null,
            status: $data['status'] ?? null,
            runtime: $data['runtime'] ?? null,
            averageRuntime: $data['averageRuntime'] ?? null,
            officialSite: $data['officialSite'] ?? null,
            rating: RatingDTO::fromArray($data['rating'] ?? null),
            summary: $data['summary'] ?? null,
            episodes: $episodes,
        );
    }
}
