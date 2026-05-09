<?php

namespace App\Modules\Shows\Domain\Shows\Contracts;

use App\Modules\Shows\Application\Shows\DTO\ExternalShowDTO;
use App\Modules\Shows\Application\Shows\DTO\ShowReferenceDTO;

interface ShowCatalogInterface
{
    public function getShow(string $showName): ExternalShowDTO;

    public function getShowByIntegrationId(int $showIntegrationId): ExternalShowDTO;

    /**
     * @return ShowReferenceDTO[]
     */
    public function getShowReferencesPage(int $page): array;
}
