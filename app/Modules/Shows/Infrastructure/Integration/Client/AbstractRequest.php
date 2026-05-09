<?php

namespace App\Modules\Shows\Infrastructure\Integration\Client;

use App\Modules\Shows\Infrastructure\Integration\Exceptions\TvMazeRateLimitException;
use App\Modules\Shows\Infrastructure\Integration\Exceptions\TvMazeServiceUnavailableException;
use App\Modules\Shows\Infrastructure\Integration\Exceptions\TvMazeTransportException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AbstractRequest
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {}

    public function getShow(string $url): array
    {
        return $this->requestJson($url);
    }

    public function getCollection(string $url, bool $allowNotFound = false): array
    {
        return $this->mapCollectionPayload(
            $this->requestJson($url, $allowNotFound),
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

    private function requestJson(string $url, bool $allowNotFound = false): ?array
    {
        try {
            $response = $this->client->request('GET', $url, [
                'http_errors' => false,
            ]);
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

        $payload = json_decode((string) $response->getBody(), true);

        if (!is_array($payload)) {
            throw new HttpException(502, 'TVMaze returned an invalid payload.');
        }

        return $payload;
    }
}
