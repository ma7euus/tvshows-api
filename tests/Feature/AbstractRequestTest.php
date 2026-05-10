<?php

namespace Tests\Feature;

use App\Modules\Shared\Infrastructure\Support\Config;
use App\Modules\Shows\Infrastructure\Integration\Client\AbstractRequest;
use App\Modules\Shows\Infrastructure\Integration\Exceptions\TvMazeRateLimitException;
use App\Modules\Shows\Infrastructure\Integration\Exceptions\TvMazeServiceUnavailableException;
use App\Modules\Shows\Infrastructure\Integration\Exceptions\TvMazeTransportException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AbstractRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function mockConfig(): Config
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('get')->with('tvmaze.client.api.base_path')->andReturn('');

        return $config;
    }

    public function test_paginated_collection_request_throws_rate_limit_exception_on_429(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $config = $this->mockConfig();

        $path = sprintf("%s/shows", $config->get('tvmaze.client.api.base_path'));
        $client->shouldReceive('request')
            ->once()
            ->withArgs(function (string $method, string $requestPath, array $options) use ($path): bool {
                return $method === 'GET'
                    && $requestPath === $path
                    && $options['query'] === ['page' => 0]
                    && isset($options['on_stats'])
                    && is_callable($options['on_stats']);
            })
            ->andReturn(new Response(429, ['Retry-After' => '10'], '[]'));

        $request = new AbstractRequest($client, $config);

        $this->expectException(TvMazeRateLimitException::class);
        $this->expectExceptionMessage('TVMaze rate limit exceeded.');

        $request->getCollection($path, [
            'query' => [
                'page' => 0,
            ]
        ], true);
    }

    public function test_paginated_show_request_throws_service_unavailable_exception_on_503(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $config = $this->mockConfig();
        $integrationId = 1;
        $path = sprintf("%s/shows/%d", $config->get('tvmaze.client.api.base_path'), $integrationId);
        $client->shouldReceive('request')
            ->once()
            ->withArgs(function (string $method, string $requestPath, array $options) use ($path): bool {
                return $method === 'GET'
                    && $requestPath === $path
                    && $options['query'] === ['embed' => 'episodes']
                    && isset($options['on_stats'])
                    && is_callable($options['on_stats']);
            })
            ->andReturn(new Response(503, [], '{"message":"temporarily unavailable"}'));

        $request = new AbstractRequest($client, $config);

        $this->expectException(TvMazeServiceUnavailableException::class);
        $this->expectExceptionMessage('TVMaze service is unavailable.');

        $request->getShowByIntegrationId(1);
    }

    public function test_request_throws_transport_exception_when_http_client_fails(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $config = $this->mockConfig();

        $path = sprintf("%s/shows", $config->get('tvmaze.client.api.base_path'));
        $client->shouldReceive('request')
            ->once()
            ->withArgs(function (string $method, string $requestPath, array $options) use ($path): bool {
                return $method === 'GET'
                    && $requestPath === $path
                    && $options['query'] === ['page' => 0]
                    && isset($options['on_stats'])
                    && is_callable($options['on_stats']);
            })
            ->andThrow(new ConnectException(
                'Connection failed.',
                new Request('GET', 'https://api.tvmaze.com/shows?page=0'),
            ));

        $request = new AbstractRequest($client, $config);

        $this->expectException(TvMazeTransportException::class);
        $this->expectExceptionMessage('TVMaze integration is unavailable.');

        $request->getCollection($path, ['query' => [
            'page' => 0,
        ]], true);
    }
}
