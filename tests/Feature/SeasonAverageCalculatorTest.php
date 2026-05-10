<?php

namespace Tests\Feature;

use App\Modules\Shows\Domain\Shows\DTO\EpisodeRatingDTO;
use App\Modules\Shows\Domain\Shows\Services\SeasonAverageCalculator;
use Tests\TestCase;

class SeasonAverageCalculatorTest extends TestCase
{
    public function test_calculate_groups_by_season_ignores_null_ratings_and_defaults_to_zero(): void
    {
        $calculator = new SeasonAverageCalculator();

        $averages = $calculator->calculate([
            new EpisodeRatingDTO(season: 2, rating: null),
            new EpisodeRatingDTO(season: 1, rating: 8.0),
            new EpisodeRatingDTO(season: 1, rating: null),
            new EpisodeRatingDTO(season: 1, rating: 6.0),
            new EpisodeRatingDTO(season: null, rating: 9.0),
        ]);

        $this->assertCount(2, $averages);
        $this->assertSame(1, $averages[0]->season);
        $this->assertSame(7.0, $averages[0]->averageRating);
        $this->assertSame(2, $averages[1]->season);
        $this->assertSame(0.0, $averages[1]->averageRating);
    }
}
