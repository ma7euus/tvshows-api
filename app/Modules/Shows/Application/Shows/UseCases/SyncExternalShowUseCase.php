<?php

namespace App\Modules\Shows\Application\Shows\UseCases;

use App\Modules\Shows\Application\Shows\DTO\ExternalShowDTO;
use App\Modules\Shows\Application\Shows\DTO\ShowSyncResultDTO;
use App\Modules\Shows\Domain\Shows\Contracts\EpisodeRepositoryInterface;
use App\Modules\Shows\Domain\Shows\Contracts\ShowRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class SyncExternalShowUseCase
{
    public function __construct(
        private readonly ShowRepositoryInterface $showRepository,
        private readonly EpisodeRepositoryInterface $episodeRepository,
    ) {}

    public function execute(ExternalShowDTO $externalShow): ShowSyncResultDTO
    {
        return DB::transaction(function () use ($externalShow) {
            $show = $this->showRepository->upsertFromExternalShow($externalShow);

            $this->episodeRepository->syncForShow($show, $externalShow->episodes);

            return new ShowSyncResultDTO(
                show: $show->fresh(['episodes'])->loadCount('episodes'),
                created: $show->wasRecentlyCreated,
            );
        });
    }
}
