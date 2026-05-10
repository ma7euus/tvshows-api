<?php

namespace App\Modules\Shows\Domain\Shows\Contracts\Repositories;

use App\Models\Show;
use App\Modules\Shows\Application\Shows\DTO\ExternalEpisodeDTO;
use App\Modules\Shows\Application\Shows\DTO\SeasonAverageDTO;

interface EpisodeRepositoryInterface
{
    /**
     * @param ExternalEpisodeDTO[] $episodes
     */
    public function syncForShow(Show $show, array $episodes): void;

    public function hasEpisodesForShow(string $showId): bool;

    /**
     * @return SeasonAverageDTO[]
     */
    public function getSeasonAveragesByShow(string $showId): array;
}
