<?php

namespace App\Modules\Shows\Application\Shows\UseCases;

use App\Modules\Shows\Application\Shows\DTO\ShowSyncResultDTO;
use App\Modules\Shows\Domain\Shows\Contracts\ShowCatalogInterface;

final class SyncShowUseCase
{
    public function __construct(
        private readonly ShowCatalogInterface $showCatalog,
        private readonly SyncExternalShowUseCase $syncExternalShowUseCase,
    ) {}

    public function execute(string $showName): ShowSyncResultDTO
    {
        return $this->syncExternalShowUseCase->execute(
            $this->showCatalog->getShow($showName),
        );
    }
}
