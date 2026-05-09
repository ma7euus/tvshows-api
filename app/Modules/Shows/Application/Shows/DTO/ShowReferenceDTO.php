<?php

namespace App\Modules\Shows\Application\Shows\DTO;

final class ShowReferenceDTO
{
    public function __construct(
        public readonly int $integrationId,
        public readonly ?string $name,
    ) {}
}
