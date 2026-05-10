<?php

namespace App\Modules\Shows\Infrastructure\Integration\Client;

use App\Modules\Shared\Infrastructure\Support\Config;
use App\Modules\Shows\Application\Shows\DTO\ExternalEpisodeDTO;
use App\Modules\Shows\Application\Shows\DTO\ExternalShowDTO;
use App\Modules\Shows\Application\Shows\DTO\ShowReferenceDTO;
use App\Modules\Shows\Domain\Shows\Contracts\ShowCatalogInterface;
use App\Modules\Shows\Infrastructure\Integration\DTO\ShowsRequestDTO;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RequestService implements ShowCatalogInterface
{

    public function __construct(
        protected AbstractRequest $abstractRequest,
        protected Config          $config
    )
    {
    }

    public function getShow(string $showName): ExternalShowDTO
    {
        return $this->mapExternalShow(
            ShowsRequestDTO::fromArray($this->abstractRequest->getShowByName($showName)),
        );
    }

    public function getShowByIntegrationId(int $showIntegrationId): ExternalShowDTO
    {
        return $this->mapExternalShow(
            ShowsRequestDTO::fromArray($this->abstractRequest->getShowByIntegrationId($showIntegrationId)),
        );
    }

    public function getShowReferencesPage(int $page): array
    {
        $path = sprintf("%s/shows", $this->config->get('tvmaze.client.api.base_path'));
        return array_values(array_filter(array_map(function (array $show): ?ShowReferenceDTO {
            $dto = ShowsRequestDTO::fromArray($show);

            if ($dto->id === null) {
                return null;
            }

            return new ShowReferenceDTO(
                integrationId: $dto->id,
                name: $dto->name,
            );
        }, $this->abstractRequest->getCollection($path, ['query' => [
            'page' => $page,
        ]], true))));
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
                static fn($episode) => $episode->id === null ? null : new ExternalEpisodeDTO(
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
