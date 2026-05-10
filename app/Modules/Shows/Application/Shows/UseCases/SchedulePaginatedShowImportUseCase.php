<?php

namespace App\Modules\Shows\Application\Shows\UseCases;

use App\Enums\TvMazeImportStatus;
use App\Jobs\ImportTvMazeShowsPageJob;
use App\Models\TvMazeImport;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class SchedulePaginatedShowImportUseCase
{
    public function execute(): TvMazeImport
    {
        $activeImport = TvMazeImport::query()
            ->active()
            ->latest('created_at')
            ->first();

        if ($activeImport !== null) {
            throw new ConflictHttpException(
                sprintf("Importation is already in progress. ID: %s", $activeImport->id)
            );
        }

        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::PENDING,
            'current_page' => 0,
            'processed_pages' => 0,
            'processed_shows' => 0,
        ]);

        ImportTvMazeShowsPageJob::dispatch($import->id, 0);

        return $import->fresh();
    }
}
