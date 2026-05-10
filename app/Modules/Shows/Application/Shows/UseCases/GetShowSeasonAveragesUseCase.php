<?php

namespace App\Modules\Shows\Application\Shows\UseCases;

use App\Modules\Shows\Application\Shows\DTO\SeasonAverageDTO;
use App\Modules\Shows\Application\Shows\DTO\ShowSeasonAverageDTO;
use App\Modules\Shows\Domain\Shows\Contracts\Repositories\EpisodeRepositoryInterface;
use App\Modules\Shows\Domain\Shows\Contracts\Repositories\ShowRepositoryInterface;
use App\Modules\Shows\Domain\Shows\DTO\EpisodeRatingDTO;
use App\Modules\Shows\Domain\Shows\Services\SeasonAverageCalculator;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class GetShowSeasonAveragesUseCase
{
    public function __construct(
        protected readonly ShowRepositoryInterface    $showRepository,
        protected readonly EpisodeRepositoryInterface $episodeRepository,
        protected readonly SeasonAverageCalculator    $seasonAverageCalculator,
    )
    {
    }

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

    public function calculate(string $showId): ShowSeasonAverageDTO
    {
        $episodes = $this->episodeRepository->getEpisodesByShow($showId);
        if (count($episodes) === 0) {
            throw new UnprocessableEntityHttpException('No episodes available for the selected show.');
        }
        $episodeRatingsDTO = array_map(fn($episode) => new EpisodeRatingDTO(
            $episode->season,
            $episode->rating
        ), $episodes);

        $seasonAveragesDTO = array_map(fn($average) => new SeasonAverageDTO(
            $average->season,
            $average->averageRating
        ), $this->seasonAverageCalculator->calculate($episodeRatingsDTO));

        return new ShowSeasonAverageDTO(
            show: $this->showRepository->findById($showId),
            averages: $seasonAveragesDTO,
        );
    }
}
