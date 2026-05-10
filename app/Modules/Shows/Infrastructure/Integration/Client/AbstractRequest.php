<?php

namespace App\Modules\Shows\Infrastructure\Integration\Client;

use App\Modules\Shared\Infrastructure\Support\Config;
use App\Modules\Shows\Infrastructure\Integration\Exceptions\TvMazeRateLimitException;
use App\Modules\Shows\Infrastructure\Integration\Exceptions\TvMazeServiceUnavailableException;
use App\Modules\Shows\Infrastructure\Integration\Exceptions\TvMazeTransportException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\TransferStats;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AbstractRequest
{
    protected string $fullUri;

    public function __construct(
        protected readonly ClientInterface $client,
        protected readonly Config          $config,
    )
    {
    }

    public function getShowByName(string $name): array
    {
        $path = sprintf("%s/singlesearch/shows", $this->config->get('tvmaze.client.api.base_path'));
        return $this->requestJson($path, [
            'query' => [
                'q' => $name,
                'embed' => 'episodes'
            ]
        ]);
    }

    public function getShowByIntegrationId(int $showIntegrationId): array
    {
        $path = sprintf("%s/shows/%d", $this->config->get('tvmaze.client.api.base_path'), $showIntegrationId);
        return $this->requestJson($path, [
            'query' => [
                'embed' => 'episodes'
            ]
        ]);
    }

    public function getCollection(string $path, array $options = [], bool $allowNotFound = false): array
    {
        return $this->mapCollectionPayload(
            $this->requestJson($path, $options, $allowNotFound),
        );
    }

    private function mapCollectionPayload(?array $payload): array
    {
        if ($payload === null) {
            return [];
        }

        if (!array_is_list($payload)) {
            throw new HttpException(502, 'TVMaze returned an invalid collection payload.');
        }

        return $payload;
    }

    private function requestJson(string $path, array $options = [], bool $allowNotFound = false): ?array
    {
        try {
            $options = array_merge($options, [
                'on_stats' => $this->buildOnStatsCallback()
            ]);
            $response = $this->client->request('GET', $path, $options);
        } catch (GuzzleException $exception) {
            throw new TvMazeTransportException(previous: $exception);
        }

        $statusCode = $response->getStatusCode();

        if ($statusCode === 404) {
            if ($allowNotFound) {
                return null;
            }

            throw new NotFoundHttpException('Show not found in TVMaze.');
        }

        if ($statusCode === 429) {
            throw new TvMazeRateLimitException(headers: $response->getHeaders());
        }

        if ($statusCode === 503) {
            throw new TvMazeServiceUnavailableException(headers: $response->getHeaders());
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new HttpException(502, 'TVMaze returned an unexpected response.');
        }

        $payload = json_decode((string)$response->getBody(), true);

        if (!is_array($payload)) {
            throw new HttpException(502, 'TVMaze returned an invalid payload.');
        }

        return $payload;
    }

    protected function buildOnStatsCallback(): \Closure
    {
        return function (TransferStats $transferStats) {
            $this->fullUri = $transferStats->getEffectiveUri();
        };
    }
}
