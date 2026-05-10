<?php

namespace App\Modules\Shows\Infrastructure\Persistence\Eloquent;

use App\Models\Show;
use App\Modules\Shared\Infrastructure\Persistence\Support\PersistenceValueNormalizer;
use App\Modules\Shows\Application\Shows\DTO\ExternalShowDTO;
use App\Modules\Shows\Domain\Shows\Contracts\Repositories\ShowRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

final class EloquentShowRepository implements ShowRepositoryInterface
{
    public function __construct(
        private readonly PersistenceValueNormalizer $valueNormalizer,
    ) {}

    private const SORTABLE_FIELDS = [
        'id',
        'id_integration',
        'name',
        'language',
        'status',
        'runtime',
        'average_runtime',
        'rating',
        'created_at',
        'updated_at',
    ];

    public function findById(string $id): Show
    {
        return Show::query()->findOrFail($id);
    }

    public function upsertFromExternalShow(ExternalShowDTO $showData): Show
    {
        return Show::query()->updateOrCreate(
            ['id_integration' => $showData->integrationId],
            [
                'name' => $this->valueNormalizer->nullableString($showData->name),
                'type' => $this->valueNormalizer->nullableString($showData->type),
                'language' => $this->valueNormalizer->nullableString($showData->language),
                'status' => $this->valueNormalizer->nullableString($showData->status),
                'runtime' => $showData->runtime,
                'average_runtime' => $showData->averageRuntime,
                'official_site' => $this->valueNormalizer->nullableString($showData->officialSite),
                'rating' => $showData->rating,
                'summary' => $this->valueNormalizer->nullableString($showData->summary),
            ],
        );
    }

    public function paginateByName(
        string $name,
        int $page,
        int $size,
        string $sortField,
        string $sortOrder,
    ): LengthAwarePaginator {
        $sortField = in_array($sortField, self::SORTABLE_FIELDS, true) ? $sortField : 'name';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        return Show::query()
            ->withCount('episodes')
            ->when($name !== '', fn ($query) => $query->where('name', 'ILIKE', "%{$name}%"))
            ->orderBy($sortField, $sortOrder)
            ->paginate($size, ['*'], 'page', $page + 1);
    }
}
