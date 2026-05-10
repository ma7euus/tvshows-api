<?php

namespace App\Modules\Shows\Application\Shows\UseCases;

use App\Modules\Shows\Domain\Shows\Contracts\Repositories\ShowRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListShowsUseCase
{
    public function __construct(
        private readonly ShowRepositoryInterface $showRepository,
    ) {}

    public function execute(
        string $name,
        int $page,
        int $size,
        string $sortField,
        string $sortOrder,
    ): LengthAwarePaginator {
        return $this->showRepository->paginateByName(
            name: $name,
            page: $page,
            size: $size,
            sortField: $sortField,
            sortOrder: $sortOrder,
        );
    }
}
