<?php

namespace App\Modules\Shows\Domain\Shows\Contracts\Repositories;

use App\Models\Show;
use App\Modules\Shows\Application\Shows\DTO\ExternalShowDTO;
use Illuminate\Pagination\LengthAwarePaginator;

interface ShowRepositoryInterface
{
    public function findById(string $id): Show;

    public function upsertFromExternalShow(ExternalShowDTO $showData): Show;

    public function paginateByName(
        string $name,
        int $page,
        int $size,
        string $sortField,
        string $sortOrder,
    ): LengthAwarePaginator;
}
