<?php

namespace App\Modules\Shows\Infrastructure\Integration\Client;

use App\Modules\Shows\Application\Shows\DTO\ExternalEpisodeDTO;
use App\Modules\Shows\Application\Shows\DTO\ExternalShowDTO;
use App\Modules\Shows\Application\Shows\DTO\ShowReferenceDTO;
use App\Modules\Shows\Domain\Shows\Contracts\ShowCatalogInterface;
use App\Modules\Shows\Infrastructure\Integration\DTO\ShowsRequestDTO;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequestService implements ShowCatalogInterface
{
    private const SEARCH_URL = 'https://api.tvmaze.com/singlesearch/shows?q=%s&embed=episodes';
    private const SHOW_BY_ID_URL = 'https://api.tvmaze.com/shows/%d?embed=episodes';
    private const SHOW_INDEX_URL = 'https://api.tvmaze.com/shows?page=%d';

    private AbstractRequest $abstractRequest;

    public function __construct(AbstractRequest $abstractRequest)
    {
        $this->abstractRequest = $abstractRequest;
    }

    public function getShow(string $showName): ExternalShowDTO
    {
        $url = sprintf(self::SEARCH_URL, urlencode($showName));

        return $this->mapExternalShow(
            ShowsRequestDTO::fromArray($this->abstractRequest->getShow($url)),
        );
    }

    public function getShowByIntegrationId(int $showIntegrationId): ExternalShowDTO
    {
        $url = sprintf(self::SHOW_BY_ID_URL, $showIntegrationId);

        return $this->mapExternalShow(
            ShowsRequestDTO::fromArray($this->abstractRequest->getShow($url)),
        );
    }

    public function getShowReferencesPage(int $page): array
    {
        $url = sprintf(self::SHOW_INDEX_URL, $page);

        return array_values(array_filter(array_map(function (array $show): ?ShowReferenceDTO {
            $dto = ShowsRequestDTO::fromArray($show);

            if ($dto->id === null) {
                return null;
            }

            return new ShowReferenceDTO(
                integrationId: $dto->id,
                name: $dto->name,
            );
        }, $this->abstractRequest->getCollection($url, true))));
    }

    private function mapExternalShow(ShowsRequestDTO $dto): ExternalShowDTO
    {
        if ($dto->id === null) {
            throw new HttpException(502, 'TVMaze payload did not include a valid show identifier.');
        }

        return new ExternalShowDTO(
            integrationId: $dto->id,
            name: $dto->name,
            type: $dto->type,
            language: $dto->language,
            status: $dto->status,
            runtime: $dto->runtime,
            averageRuntime: $dto->averageRuntime,
            officialSite: $dto->officialSite,
            rating: $dto->rating?->average,
            summary: $dto->summary,
            episodes: array_values(array_filter(array_map(
                static fn ($episode) => $episode->id === null ? null : new ExternalEpisodeDTO(
                    integrationId: $episode->id,
                    name: $episode->name,
                    season: $episode->season,
                    number: $episode->number,
                    type: $episode->type,
                    airdate: $episode->airdate,
                    airtime: $episode->airtime,
                    airstamp: $episode->airstamp,
                    runtime: $episode->runtime,
                    rating: $episode->rating?->average,
                    summary: $episode->summary,
                ),
                $dto->episodes,
            ))),
        );
    }
}
