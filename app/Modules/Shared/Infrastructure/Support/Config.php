<?php

namespace App\Modules\Shared\Infrastructure\Support;

class Config
{
    public function get(string $key): mixed
    {
        return config($key);
    }
}