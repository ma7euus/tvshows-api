<?php

namespace App\Modules\Shows\Application\Shows\UseCases;

use App\Enums\TvMazeImportStatus;
use App\Jobs\ImportTvMazeShowsPageJob;
use App\Models\TvMazeImport;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ResumePaginatedShowImportUseCase
{
    public function execute(TvMazeImport $import): TvMazeImport
    {
        if ($import->status !== TvMazeImportStatus::ERROR) {
            throw new ConflictHttpException(
                sprintf(
                    "Importation cannot be resumed from status '%s'. ID: %s",
                    $import->status->value,
                    $import->id,
                ),
            );
        }

        $activeImport = TvMazeImport::query()
            ->active()
            ->whereKeyNot($import->id)
            ->latest('created_at')
            ->first();

        if ($activeImport !== null) {
            throw new ConflictHttpException(
                sprintf("Importation is already in progress. ID: %s", $activeImport->id)
            );
        }

        $resumePage = $this->resolveResumePage($import);

        $import->forceFill([
            'status' => TvMazeImportStatus::PENDING,
            'error_message' => null,
            'finished_at' => null,
        ])->save();

        ImportTvMazeShowsPageJob::dispatch($import->id, $resumePage);

        return $import->fresh();
    }

    private function resolveResumePage(TvMazeImport $import): int
    {
        return $import->processed_pages === $import->current_page + 1
            ? $import->current_page + 1
            : $import->current_page;
    }
}
