<?php

namespace App\Modules\Shows\Domain\Shows\Services;

use App\Modules\Shows\Domain\Shows\DTO\EpisodeRatingDTO;
use App\Modules\Shows\Domain\Shows\DTO\SeasonAverageDTO;

class SeasonAverageCalculator
{
    /**
     * @param EpisodeRatingDTO[] $episodeRatings
     * @return SeasonAverageDTO[]
     */
    public function calculate(array $episodeRatings): array
    {
        $groupedBySeason = [];

        foreach ($episodeRatings as $episodeRating) {
            if ($episodeRating->season === null) {
                continue;
            }

            $groupedBySeason[$episodeRating->season] ??= [
                'sum' => 0.0,
                'count' => 0,
            ];

            if ($episodeRating->rating !== null) {
                $groupedBySeason[$episodeRating->season]['sum'] += $episodeRating->rating;
                $groupedBySeason[$episodeRating->season]['count']++;
            }
        }

        ksort($groupedBySeason);

        return array_map(
            static fn (int $season, array $values) => new SeasonAverageDTO(
                season: $season,
                averageRating: $values['count'] > 0
                    ? round($values['sum'] / $values['count'], 2)
                    : 0.0,
            ),
            array_keys($groupedBySeason),
            array_values($groupedBySeason),
        );
    }
}
