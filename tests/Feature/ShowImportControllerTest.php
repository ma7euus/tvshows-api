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

    public function test_user_cannot_schedule_or_view_paginated_import(): void
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
    }

    public function test_cannot_schedule_paginated_import_while_another_is_active(): void
    {
        Queue::fake();

        TvMazeImport::query()->create([
            'status' => TvMazeImportStatus::RUNNING,
            'started_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows/imports/paginated');

        $response->assertStatus(409)
            ->assertJsonPath('message', 'A TVMaze paginated import is already in progress.');

        Queue::assertNothingPushed();
    }
}
