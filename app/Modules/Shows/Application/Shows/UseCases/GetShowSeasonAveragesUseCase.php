<?php

namespace App\Modules\Shows\Application\Shows\UseCases;

use App\Modules\Shows\Application\Shows\DTO\ShowSeasonAverageDTO;
use App\Modules\Shows\Domain\Shows\Contracts\Repositories\EpisodeRepositoryInterface;
use App\Modules\Shows\Domain\Shows\Contracts\Repositories\ShowRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class GetShowSeasonAveragesUseCase
{
    public function __construct(
        private readonly ShowRepositoryInterface $showRepository,
        private readonly EpisodeRepositoryInterface $episodeRepository,
    ) {}

    public function execute(string $showId): ShowSeasonAverageDTO
    {
        $show = $this->showRepository->findById($showId);

        if (!$this->episodeRepository->hasEpisodesForShow($showId)) {
            throw new UnprocessableEntityHttpException('No episodes available for the selected show.');
        }

        return new ShowSeasonAverageDTO(
            show: $show,
            averages: $this->episodeRepository->getSeasonAveragesByShow($showId),
        );
    }
}
