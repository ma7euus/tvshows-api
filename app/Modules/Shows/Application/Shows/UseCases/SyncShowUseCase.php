<?php

namespace App\Modules\Shows\Application\Shows\UseCases;

use App\Modules\Shows\Application\Shows\DTO\ShowSyncResultDTO;
use App\Modules\Shows\Domain\Shows\Contracts\ShowCatalogInterface;

class SyncShowUseCase
{
    public function __construct(
        protected readonly ShowCatalogInterface $showCatalog,
        protected readonly SyncExternalShowUseCase $syncExternalShowUseCase,
    ) {}

    public function execute(string $showName): ShowSyncResultDTO
    {
        return $this->syncExternalShowUseCase->execute(
            $this->showCatalog->getShow($showName),
        );
    }
}
