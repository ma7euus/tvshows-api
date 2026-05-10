<?php

namespace Tests\Feature;

use App\Enums\TvMazeImportStatus;
use App\Jobs\ImportTvMazeShowsPageJob;
use App\Models\Episode;
use App\Models\Show;
use App\Models\TvMazeImport;
use App\Modules\Shows\Application\Shows\DTO\ExternalEpisodeDTO;
use App\Modules\Shows\Application\Shows\DTO\ExternalShowDTO;
use App\Modules\Shows\Application\Shows\DTO\ShowReferenceDTO;
use App\Modules\Shows\Application\Shows\UseCases\SyncExternalShowUseCase;
use App\Modules\Shows\Domain\Shows\Contracts\ShowCatalogInterface;
use App\Modules\Shows\Infrastructure\Integration\Exceptions\TvMazeRateLimitException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ImportTvMazeShowsPageJobTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_job_imports_page_and_dispatches_next_page(): void
    {
        Queue::fake();

        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::PENDING,
        ]);

        $catalog = Mockery::mock(ShowCatalogInterface::class);
        $catalog->shouldReceive('getShowReferencesPage')
            ->once()
            ->with(0)
            ->andReturn([
                new ShowReferenceDTO(100, 'Dark'),
                new ShowReferenceDTO(200, 'Lost'),
            ]);
        $catalog->shouldReceive('getShowByIntegrationId')
            ->once()
            ->with(100)
            ->andReturn($this->externalShowData(100, 'Dark', 1001));
        $catalog->shouldReceive('getShowByIntegrationId')
            ->once()
            ->with(200)
            ->andReturn($this->externalShowData(200, 'Lost', 2001));

        $job = new ImportTvMazeShowsPageJob($import->id, 0);
        $job->handle($catalog, $this->app->make(SyncExternalShowUseCase::class));

        $import->refresh();

        $this->assertSame(TvMazeImportStatus::RUNNING, $import->status);
        $this->assertSame(0, $import->current_page);
        $this->assertSame(1, $import->processed_pages);
        $this->assertSame(2, $import->processed_shows);
        $this->assertNotNull($import->started_at);
        $this->assertNull($import->finished_at);
        $this->assertSame(2, Show::query()->count());
        $this->assertSame(2, Episode::query()->count());

        Queue::assertPushed(ImportTvMazeShowsPageJob::class, function (ImportTvMazeShowsPageJob $nextJob) use ($import) {
            return $nextJob->importId === $import->id && $nextJob->page === 1;
        });
    }

    public function test_job_normalizes_blank_episode_time_fields_before_persisting(): void
    {
        Queue::fake();

        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::PENDING,
        ]);

        $catalog = Mockery::mock(ShowCatalogInterface::class);
        $catalog->shouldReceive('getShowReferencesPage')
            ->once()
            ->with(0)
            ->andReturn([
                new ShowReferenceDTO(300, 'The Chronicle'),
            ]);
        $catalog->shouldReceive('getShowByIntegrationId')
            ->once()
            ->with(300)
            ->andReturn($this->externalShowData(
                integrationId: 300,
                name: 'The Chronicle',
                episodeIntegrationId: 398479,
                airtime: '',
                airstamp: '',
                summary: '',
            ));

        $job = new ImportTvMazeShowsPageJob($import->id, 0);
        $job->handle($catalog, $this->app->make(SyncExternalShowUseCase::class));

        $episode = Episode::query()->where('id_integration', 398479)->firstOrFail();

        $this->assertSame('2001-10-10', $episode->airdate?->format('Y-m-d'));
        $this->assertNull($episode->airtime);
        $this->assertNull($episode->airstamp);
        $this->assertNull($episode->summary);
    }

    public function test_job_marks_import_complete_when_page_is_empty(): void
    {
        Queue::fake();

        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::PENDING,
        ]);

        $catalog = Mockery::mock(ShowCatalogInterface::class);
        $catalog->shouldReceive('getShowReferencesPage')
            ->once()
            ->with(0)
            ->andReturn([]);

        $job = new ImportTvMazeShowsPageJob($import->id, 0);
        $job->handle($catalog, $this->app->make(SyncExternalShowUseCase::class));

        $import->refresh();

        $this->assertSame(TvMazeImportStatus::COMPLETE, $import->status);
        $this->assertNotNull($import->started_at);
        $this->assertNotNull($import->finished_at);
        $this->assertSame(0, $import->processed_pages);
        $this->assertSame(0, $import->processed_shows);

        Queue::assertNothingPushed();
    }

    public function test_job_skips_shows_without_episodes_without_failing_import(): void
    {
        Queue::fake();

        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::PENDING,
        ]);

        $catalog = Mockery::mock(ShowCatalogInterface::class);
        $catalog->shouldReceive('getShowReferencesPage')
            ->once()
            ->with(0)
            ->andReturn([
                new ShowReferenceDTO(400, 'No Episodes Show'),
                new ShowReferenceDTO(500, 'Imported Show'),
            ]);
        $catalog->shouldReceive('getShowByIntegrationId')
            ->once()
            ->with(400)
            ->andReturn(new ExternalShowDTO(
                integrationId: 400,
                name: 'No Episodes Show',
                type: 'Scripted',
                language: 'English',
                status: 'Running',
                runtime: 60,
                averageRuntime: 58,
                officialSite: 'https://example.com/no-episodes-show',
                rating: 7.2,
                summary: 'No episodes available.',
                episodes: [],
            ));
        $catalog->shouldReceive('getShowByIntegrationId')
            ->once()
            ->with(500)
            ->andReturn($this->externalShowData(500, 'Imported Show', 5001));

        $job = new ImportTvMazeShowsPageJob($import->id, 0);
        $job->handle($catalog, $this->app->make(SyncExternalShowUseCase::class));

        $import->refresh();

        $this->assertSame(TvMazeImportStatus::RUNNING, $import->status);
        $this->assertSame(1, $import->processed_pages);
        $this->assertSame(1, $import->processed_shows);
        $this->assertSame(1, Show::query()->count());
        $this->assertSame(1, Episode::query()->count());
        $this->assertNull(Show::query()->where('id_integration', 400)->first());

        Queue::assertPushed(ImportTvMazeShowsPageJob::class, function (ImportTvMazeShowsPageJob $nextJob) use ($import) {
            return $nextJob->importId === $import->id && $nextJob->page === 1;
        });
    }

    public function test_job_rethrows_transient_tvmaze_exception_for_queue_retry(): void
    {
        Queue::fake();

        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::PENDING,
        ]);

        $catalog = Mockery::mock(ShowCatalogInterface::class);
        $catalog->shouldReceive('getShowReferencesPage')
            ->once()
            ->with(0)
            ->andThrow(new TvMazeRateLimitException());

        $job = (new ImportTvMazeShowsPageJob($import->id, 0))
            ->withFakeQueueInteractions();

        $this->expectException(TvMazeRateLimitException::class);
        $this->expectExceptionMessage('TVMaze rate limit exceeded.');

        try {
            $job->handle($catalog, $this->app->make(SyncExternalShowUseCase::class));
        } finally {
            $job->assertNotFailed();

            $import->refresh();

            $this->assertSame(TvMazeImportStatus::RUNNING, $import->status);
            $this->assertNull($import->error_message);
            $this->assertNull($import->finished_at);
        }
    }

    public function test_job_fails_immediately_on_non_retryable_exception(): void
    {
        Queue::fake();

        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::PENDING,
        ]);

        $catalog = Mockery::mock(ShowCatalogInterface::class);
        $catalog->shouldReceive('getShowReferencesPage')
            ->once()
            ->with(0)
            ->andThrow(new RuntimeException('Unexpected payload shape.'));

        $job = (new ImportTvMazeShowsPageJob($import->id, 0))
            ->withFakeQueueInteractions();

        $job->handle($catalog, $this->app->make(SyncExternalShowUseCase::class));
        $job->assertFailedWith(new RuntimeException('Unexpected payload shape.'));
    }

    public function test_failed_marks_import_as_error_with_reason(): void
    {
        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::RUNNING,
            'started_at' => now()->subMinute(),
        ]);

        $job = new ImportTvMazeShowsPageJob($import->id, 0);
        $job->failed(new TvMazeRateLimitException('TVMaze rate limit exceeded after queued retries.'));

        $import->refresh();

        $this->assertSame(TvMazeImportStatus::ERROR, $import->status);
        $this->assertSame('TVMaze rate limit exceeded after queued retries.', $import->error_message);
        $this->assertNotNull($import->finished_at);
    }

    public function test_job_exposes_queue_backoff_strategy(): void
    {
        Queue::fake();

        config()->set('tvmaze.queue.backoff_seconds', [3, 9, 27]);

        $job = new ImportTvMazeShowsPageJob('import-id', 0);

        $this->assertSame([3, 9, 27], $job->backoff());
        $this->assertSame((int) config('tvmaze.queue.max_attempts'), $job->tries);

        Queue::assertNothingPushed();
    }

    private function externalShowData(
        int $integrationId,
        string $name,
        int $episodeIntegrationId,
        string $airtime = '21:00',
        string $airstamp = '2024-01-01T21:00:00+00:00',
        string $summary = 'episode summary',
    ): ExternalShowDTO
    {
        return new ExternalShowDTO(
            integrationId: $integrationId,
            name: $name,
            type: 'Scripted',
            language: 'English',
            status: 'Running',
            runtime: 60,
            averageRuntime: 58,
            officialSite: 'https://example.com/' . strtolower($name),
            rating: 8.7,
            summary: $name . ' summary',
            episodes: [
                new ExternalEpisodeDTO(
                    integrationId: $episodeIntegrationId,
                    name: $name . ' Episode',
                    season: 1,
                    number: 1,
                    type: 'regular',
                    airdate: '2001-10-10',
                    airtime: $airtime,
                    airstamp: $airstamp,
                    runtime: 52,
                    rating: 8.5,
                    summary: $summary === 'episode summary' ? $name . ' episode summary' : $summary,
                ),
            ],
        );
    }
}
