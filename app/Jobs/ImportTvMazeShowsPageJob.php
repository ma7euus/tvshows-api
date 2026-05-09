<?php

namespace App\Jobs;

use App\Application\Shows\UseCases\SyncExternalShowUseCase;
use App\Domain\Shows\Contracts\ShowCatalogInterface;
use App\Enums\TvMazeImportStatus;
use App\Integration\Exceptions\TransientTvMazeException;
use App\Models\TvMazeImport;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class ImportTvMazeShowsPageJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public int $tries;

    public function __construct(
        public readonly string $importId,
        public readonly int $page,
    ) {
        $this->tries = max(1, (int) config('tvmaze.queue.max_attempts', 6));
    }

    public function backoff(): array
    {
        $backoff = config('tvmaze.queue.backoff_seconds', [5, 15, 30, 60, 120]);

        if (!is_array($backoff) || $backoff === []) {
            return [5, 15, 30, 60, 120];
        }

        return array_values(array_map(
            static fn (mixed $value): int => max(0, (int) $value),
            $backoff,
        ));
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addMinutes(
            max(1, (int) config('tvmaze.queue.retry_until_minutes', 30)),
        );
    }

    public function handle(
        ShowCatalogInterface $showCatalog,
        SyncExternalShowUseCase $syncExternalShowUseCase,
    ): void {
        $import = TvMazeImport::query()->findOrFail($this->importId);

        if (in_array($import->status, [TvMazeImportStatus::COMPLETE, TvMazeImportStatus::ERROR], true)) {
            return;
        }

        $import->forceFill([
            'status' => TvMazeImportStatus::RUNNING,
            'started_at' => $import->started_at ?? now(),
            'current_page' => $this->page,
            'error_message' => null,
        ])->save();

        try {
            $shows = $showCatalog->getShowReferencesPage($this->page);

            if ($shows === []) {
                $import->forceFill([
                    'status' => TvMazeImportStatus::COMPLETE,
                    'finished_at' => now(),
                ])->save();

                return;
            }

            foreach ($shows as $showReference) {
                $syncExternalShowUseCase->execute(
                    $showCatalog->getShowByIntegrationId($showReference->integrationId),
                );
            }

            $import->increment('processed_pages');
            $import->increment('processed_shows', count($shows));
            $import->forceFill([
                'current_page' => $this->page,
            ])->save();

            self::dispatch($this->importId, $this->page + 1);
        } catch (Throwable $exception) {
            if ($this->shouldRetry($exception)) {
                throw $exception;
            }

            $this->fail($exception);
        }
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception === null) {
            return;
        }

        $import = TvMazeImport::query()->find($this->importId);

        if ($import === null || $import->status === TvMazeImportStatus::COMPLETE) {
            return;
        }

        $import->forceFill([
            'status' => TvMazeImportStatus::ERROR,
            'error_message' => $exception->getMessage(),
            'finished_at' => now(),
        ])->save();
    }

    private function shouldRetry(Throwable $exception): bool
    {
        return $exception instanceof TransientTvMazeException;
    }
}
