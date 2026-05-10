<?php

namespace App\Modules\Shows\Infrastructure\Persistence\Eloquent;

use App\Models\Episode;
use App\Models\Show;
use App\Modules\Shared\Infrastructure\Persistence\Support\PersistenceValueNormalizer;
use App\Modules\Shows\Application\Shows\DTO\SeasonAverageDTO;
use App\Modules\Shows\Domain\Shows\Contracts\Repositories\EpisodeRepositoryInterface;
use Illuminate\Support\Collection;

final class EloquentEpisodeRepository implements EpisodeRepositoryInterface
{
    public function __construct(
        private readonly PersistenceValueNormalizer $valueNormalizer,
    ) {}

    public function syncForShow(Show $show, array $episodes): void
    {
        $integrationIds = [];

        foreach ($episodes as $episodeData) {
            $integrationIds[] = $episodeData->integrationId;

            Episode::query()->updateOrCreate(
                ['id_integration' => $episodeData->integrationId],
                [
                    'show_id' => $show->id,
                    'name' => $this->valueNormalizer->nullableString($episodeData->name),
                    'season' => $episodeData->season,
                    'number' => $episodeData->number,
                    'type' => $this->valueNormalizer->nullableString($episodeData->type),
                    'airdate' => $this->valueNormalizer->nullableString($episodeData->airdate),
                    'airtime' => $this->valueNormalizer->nullableString($episodeData->airtime),
                    'airstamp' => $this->valueNormalizer->nullableString($episodeData->airstamp),
                    'runtime' => $episodeData->runtime,
                    'rating' => $episodeData->rating,
                    'summary' => $this->valueNormalizer->nullableString($episodeData->summary),
                ],
            );
        }

        if ($integrationIds === []) {
            Episode::query()
                ->where('show_id', $show->id)
                ->delete();

            return;
        }

        Episode::query()
            ->where('show_id', $show->id)
            ->whereNotIn('id_integration', $integrationIds)
            ->delete();
    }

    public function hasEpisodesForShow(string $showId): bool
    {
        return Episode::query()
            ->where('show_id', $showId)
            ->exists();
    }

    public function getSeasonAveragesByShow(string $showId): array
    {
        return Episode::query()
            ->selectRaw('season, COALESCE(AVG(rating), 0) as average_rating')
            ->where('show_id', $showId)
            ->whereNotNull('season')
            ->groupBy('season')
            ->orderBy('season')
            ->get()
                ->map(fn ($average) => new SeasonAverageDTO(
                season: (int) $average->season,
                averageRating: round((float) $average->average_rating, 2),
            ))
            ->all();
    }

    public function getEpisodesByShow(string $showId): array
    {
        return Episode::query()
            ->where('show_id', $showId)
            ->orderBy('season', 'desc')
            ->orderBy('number', 'desc')
            ->get()->all();
    }
}
