<?php

namespace App\Providers;

use App\Modules\Shared\Domain\Security\Contracts\RoleAuthorizerInterface;
use App\Modules\Shared\Infrastructure\Security\RolePermissionService;
use App\Modules\Shows\Domain\Shows\Contracts\EpisodeRepositoryInterface;
use App\Modules\Shows\Domain\Shows\Contracts\ShowCatalogInterface;
use App\Modules\Shows\Domain\Shows\Contracts\ShowRepositoryInterface;
use App\Modules\Shows\Infrastructure\Integration\Client\AbstractRequest;
use App\Modules\Shows\Infrastructure\Integration\Client\RequestService;
use App\Modules\Shows\Infrastructure\Persistence\Eloquent\EloquentEpisodeRepository;
use App\Modules\Shows\Infrastructure\Persistence\Eloquent\EloquentShowRepository;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ClientInterface::class, function () {
            return new Client([
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'timeout' => 10,
                'connect_timeout' => 5,
            ]);
        });

        $this->app->singleton(AbstractRequest::class, function ($app) {
            return new AbstractRequest($app->make(ClientInterface::class));
        });

        $this->app->singleton(RequestService::class, function ($app) {
            return new RequestService($app->make(AbstractRequest::class));
        });

        $this->app->bind(RoleAuthorizerInterface::class, RolePermissionService::class);
        $this->app->bind(ShowCatalogInterface::class, RequestService::class);
        $this->app->bind(ShowRepositoryInterface::class, EloquentShowRepository::class);
        $this->app->bind(EpisodeRepositoryInterface::class, EloquentEpisodeRepository::class);

    }

    public function boot(): void
    {
        //
    }
}
