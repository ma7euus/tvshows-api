<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\TvMazeImportStatus;
use App\Jobs\ImportTvMazeShowsPageJob;
use App\Models\TvMazeImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShowImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $adminToken;
    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::create([
            'username' => 'admin-import',
            'password' => bcrypt('password'),
            'role' => Role::ADMIN->value,
            'enabled' => true,
        ]);

        $user = User::create([
            'username' => 'user-import',
            'password' => bcrypt('password'),
            'role' => Role::USER->value,
            'enabled' => true,
        ]);

        $this->adminToken = JWTAuth::fromUser($admin);
        $this->userToken = JWTAuth::fromUser($user);
    }

    public function test_admin_can_schedule_paginated_tvmaze_import(): void
    {
        Queue::fake();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows/imports/paginated');

        $response->assertStatus(202)
            ->assertJsonPath('status', TvMazeImportStatus::PENDING->value)
            ->assertJsonPath('currentPage', 0)
            ->assertJsonPath('processedPages', 0)
            ->assertJsonPath('processedShows', 0);

        $import = TvMazeImport::query()->firstOrFail();

        $this->assertSame(TvMazeImportStatus::PENDING, $import->status);

        Queue::assertPushed(ImportTvMazeShowsPageJob::class, function (ImportTvMazeShowsPageJob $job) use ($import) {
            return $job->importId === $import->id && $job->page === 0;
        });
    }

    public function test_admin_can_view_paginated_tvmaze_import_status(): void
    {
        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::ERROR,
            'current_page' => 12,
            'processed_pages' => 12,
            'processed_shows' => 301,
            'error_message' => 'TVMaze rate limit reached.',
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/shows/imports/' . $import->id);

        $response->assertOk()
            ->assertJsonPath('id', $import->id)
            ->assertJsonPath('status', TvMazeImportStatus::ERROR->value)
            ->assertJsonPath('currentPage', 12)
            ->assertJsonPath('processedPages', 12)
            ->assertJsonPath('processedShows', 301)
            ->assertJsonPath('errorMessage', 'TVMaze rate limit reached.');
    }

    public function test_admin_can_resume_failed_paginated_import_from_current_page(): void
    {
        Queue::fake();

        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::ERROR,
            'current_page' => 12,
            'processed_pages' => 12,
            'processed_shows' => 301,
            'error_message' => 'TVMaze rate limit reached.',
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows/imports/' . $import->id . '/resume');

        $response->assertStatus(202)
            ->assertJsonPath('id', $import->id)
            ->assertJsonPath('status', TvMazeImportStatus::PENDING->value)
            ->assertJsonPath('currentPage', 12)
            ->assertJsonPath('processedPages', 12)
            ->assertJsonPath('processedShows', 301)
            ->assertJsonPath('errorMessage', null);

        $import->refresh();

        $this->assertSame(TvMazeImportStatus::PENDING, $import->status);
        $this->assertNull($import->error_message);
        $this->assertNull($import->finished_at);

        Queue::assertPushed(ImportTvMazeShowsPageJob::class, function (ImportTvMazeShowsPageJob $job) use ($import) {
            return $job->importId === $import->id && $job->page === 12;
        });
    }

    public function test_admin_can_resume_failed_paginated_import_from_next_page_when_last_page_completed(): void
    {
        Queue::fake();

        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::ERROR,
            'current_page' => 12,
            'processed_pages' => 13,
            'processed_shows' => 325,
            'error_message' => 'Queue dispatch failed.',
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows/imports/' . $import->id . '/resume');

        $response->assertStatus(202)
            ->assertJsonPath('id', $import->id)
            ->assertJsonPath('status', TvMazeImportStatus::PENDING->value)
            ->assertJsonPath('currentPage', 12)
            ->assertJsonPath('processedPages', 13)
            ->assertJsonPath('processedShows', 325)
            ->assertJsonPath('errorMessage', null);

        Queue::assertPushed(ImportTvMazeShowsPageJob::class, function (ImportTvMazeShowsPageJob $job) use ($import) {
            return $job->importId === $import->id && $job->page === 13;
        });
    }

    public function test_user_cannot_schedule_view_or_resume_paginated_import(): void
    {
        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::PENDING,
        ]);

        $scheduleResponse = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->postJson('/api/shows/imports/paginated');

        $scheduleResponse->assertStatus(403);

        $statusResponse = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->getJson('/api/shows/imports/' . $import->id);

        $statusResponse->assertStatus(403);

        $resumeResponse = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->postJson('/api/shows/imports/' . $import->id . '/resume');

        $resumeResponse->assertStatus(403);
    }

    public function test_cannot_schedule_paginated_import_while_another_is_active(): void
    {
        Queue::fake();

        $activeImport = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::RUNNING,
            'started_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows/imports/paginated');

        $response->assertStatus(409)
            ->assertJsonPath('message',  sprintf("Importation is already in progress. ID: %s", $activeImport->id));

        Queue::assertNothingPushed();
    }

    public function test_cannot_resume_failed_import_while_another_is_active(): void
    {
        Queue::fake();

        $failedImport = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::ERROR,
            'current_page' => 12,
            'processed_pages' => 12,
            'processed_shows' => 301,
            'error_message' => 'TVMaze rate limit reached.',
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
        ]);

        $activeImport = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::RUNNING,
            'started_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows/imports/' . $failedImport->id . '/resume');

        $response->assertStatus(409)
            ->assertJsonPath('message', sprintf("Importation is already in progress. ID: %s", $activeImport->id));

        Queue::assertNothingPushed();
    }

    public function test_cannot_resume_import_from_non_failed_status(): void
    {
        Queue::fake();

        $import = TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::COMPLETE,
            'current_page' => 12,
            'processed_pages' => 13,
            'processed_shows' => 325,
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows/imports/' . $import->id . '/resume');

        $response->assertStatus(409)
            ->assertJsonPath('message', sprintf(
                "Importation cannot be resumed from status '%s'. ID: %s",
                TvMazeImportStatus::COMPLETE->value,
                $import->id,
            ));

        Queue::assertNothingPushed();
    }
}
