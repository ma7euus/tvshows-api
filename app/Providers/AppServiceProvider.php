<?php

namespace App\Providers;

use App\Integration\Client\AbstractRequest;
use App\Integration\Client\RequestService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AbstractRequest::class, function () {
            return new AbstractRequest();
        });

        $this->app->singleton(RequestService::class, function ($app) {
            return new RequestService($app->make(AbstractRequest::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
