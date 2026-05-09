<?php

namespace Tests\Feature;

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

    public function test_paginated_collection_request_throws_rate_limit_exception_on_429(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('request')
            ->once()
            ->with('GET', 'https://api.tvmaze.com/shows?page=0', ['http_errors' => false])
            ->andReturn(new Response(429, ['Retry-After' => '10'], '[]'));

        $request = new AbstractRequest($client);

        $this->expectException(TvMazeRateLimitException::class);
        $this->expectExceptionMessage('TVMaze rate limit exceeded.');

        $request->getCollection('https://api.tvmaze.com/shows?page=0', true);
    }

    public function test_paginated_show_request_throws_service_unavailable_exception_on_503(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('request')
            ->once()
            ->with('GET', 'https://api.tvmaze.com/shows/1?embed=episodes', ['http_errors' => false])
            ->andReturn(new Response(503, [], '{"message":"temporarily unavailable"}'));

        $request = new AbstractRequest($client);

        $this->expectException(TvMazeServiceUnavailableException::class);
        $this->expectExceptionMessage('TVMaze service is unavailable.');

        $request->getShow('https://api.tvmaze.com/shows/1?embed=episodes');
    }

    public function test_request_throws_transport_exception_when_http_client_fails(): void
    {
        $client = Mockery::mock(ClientInterface::class);
        $client->shouldReceive('request')
            ->once()
            ->with('GET', 'https://api.tvmaze.com/shows?page=0', ['http_errors' => false])
            ->andThrow(new ConnectException(
                'Connection failed.',
                new Request('GET', 'https://api.tvmaze.com/shows?page=0'),
            ));

        $request = new AbstractRequest($client);

        $this->expectException(TvMazeTransportException::class);
        $this->expectExceptionMessage('TVMaze integration is unavailable.');

        $request->getCollection('https://api.tvmaze.com/shows?page=0', true);
    }
}
