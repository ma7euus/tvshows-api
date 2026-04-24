<?php

namespace App\Integration\Client;

/**
 * Serviço para consumo da API TVMaze.
 * Equivalente ao RequestService.java do projeto Spring Boot.
 */
class RequestService
{
    private const URL = 'https://api.tvmaze.com/singlesearch/shows?q=%s&embed=episodes';

    private AbstractRequest $abstractRequest;

    public function __construct(AbstractRequest $abstractRequest)
    {
        $this->abstractRequest = $abstractRequest;
    }

    /**
     * Busca um show pelo nome na API TVMaze.
     *
     * @param string $showName Nome do show para busca
     * @return array|null Dados do show com episódios
     */
    public function getShow(string $showName): ?array
    {
        $url = sprintf(self::URL, urlencode($showName));
        return $this->abstractRequest->getShow($url);
    }
}
