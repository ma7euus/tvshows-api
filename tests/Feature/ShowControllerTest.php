<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Episode;
use App\Models\Show;
use App\Models\User;
use App\Modules\Shows\Application\Shows\DTO\ExternalEpisodeDTO;
use App\Modules\Shows\Application\Shows\DTO\ExternalShowDTO;
use App\Modules\Shows\Domain\Shows\Contracts\ShowCatalogInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShowControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $adminToken;
    private string $userToken;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::create([
            'username' => 'admin-test',
            'password' => bcrypt('password'),
            'role' => Role::ADMIN->value,
            'enabled' => true,
        ]);

        $user = User::create([
            'username' => 'user-test',
            'password' => bcrypt('password'),
            'role' => Role::USER->value,
            'enabled' => true,
        ]);

        $this->adminToken = JWTAuth::fromUser($admin);
        $this->userToken = JWTAuth::fromUser($user);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_admin_can_sync_show_and_avoid_duplicates(): void
    {
        $catalog = Mockery::mock(ShowCatalogInterface::class);
        $catalog->shouldReceive('getShow')
            ->twice()
            ->with('Dark')
            ->andReturn($this->externalShowData());

        $this->app->instance(ShowCatalogInterface::class, $catalog);

        $firstResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows', ['name' => 'Dark']);

        $firstResponse->assertStatus(201)
            ->assertJsonPath('name', 'Dark')
            ->assertJsonPath('episodesCount', 2);

        $secondResponse = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows', ['name' => 'Dark']);

        $secondResponse->assertStatus(200)
            ->assertJsonPath('name', 'Dark')
            ->assertJsonPath('episodesCount', 2);

        $this->assertEquals(1, Show::query()->count());
        $this->assertEquals(2, Episode::query()->count());
    }

    public function test_user_cannot_sync_show(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->postJson('/api/shows', ['name' => 'Dark']);

        $response->assertStatus(403);
    }

    public function test_disabled_user_with_valid_token_cannot_access_protected_show_route(): void
    {
        $disabledUser = User::create([
            'username' => 'disabled-show-user',
            'password' => bcrypt('password'),
            'role' => Role::USER->value,
            'enabled' => false,
        ]);

        $disabledToken = JWTAuth::fromUser($disabledUser);

        $response = $this->withHeader('Authorization', 'Bearer ' . $disabledToken)
            ->getJson('/api/shows');

        $response->assertStatus(401)
            ->assertJsonPath('message', 'User not found or disabled.');
    }

    public function test_admin_sync_normalizes_blank_optional_show_fields(): void
    {
        $catalog = Mockery::mock(ShowCatalogInterface::class);
        $catalog->shouldReceive('getShow')
            ->once()
            ->with('Blank Show')
            ->andReturn(new ExternalShowDTO(
                integrationId: 101,
                name: 'Blank Show',
                type: '',
                language: '   ',
                status: '',
                runtime: 30,
                averageRuntime: 29,
                officialSite: '',
                rating: 7.4,
                summary: '   ',
                episodes: [
                    new ExternalEpisodeDTO(
                        integrationId: 1011,
                        name: 'Blank Show Episode',
                        season: 1,
                        number: 1,
                        type: 'regular',
                        airdate: '2024-01-01',
                        airtime: '21:00',
                        airstamp: '2024-01-01T21:00:00+00:00',
                        runtime: 30,
                        rating: 7.0,
                        summary: 'Episode summary',
                    ),
                ],
            ));

        $this->app->instance(ShowCatalogInterface::class, $catalog);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows', ['name' => 'Blank Show']);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Blank Show')
            ->assertJsonPath('type', null)
            ->assertJsonPath('language', null)
            ->assertJsonPath('status', null)
            ->assertJsonPath('officialSite', null)
            ->assertJsonPath('summary', null);

        $show = Show::query()->where('id_integration', 101)->firstOrFail();

        $this->assertNull($show->type);
        $this->assertNull($show->language);
        $this->assertNull($show->status);
        $this->assertNull($show->official_site);
        $this->assertNull($show->summary);
    }

    public function test_admin_cannot_sync_show_without_episodes(): void
    {
        $catalog = Mockery::mock(ShowCatalogInterface::class);
        $catalog->shouldReceive('getShow')
            ->once()
            ->with('No Episodes Show')
            ->andReturn(new ExternalShowDTO(
                integrationId: 102,
                name: 'No Episodes Show',
                type: 'Scripted',
                language: 'English',
                status: 'Running',
                runtime: 30,
                averageRuntime: 30,
                officialSite: 'https://example.com/no-episodes-show',
                rating: 7.4,
                summary: 'No episodes available.',
                episodes: [],
            ));

        $this->app->instance(ShowCatalogInterface::class, $catalog);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/shows', ['name' => 'No Episodes Show']);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'No episodes available for the selected show.');

        $this->assertNull(Show::query()->where('id_integration', 102)->first());
        $this->assertSame(0, Episode::query()->count());
    }

    public function test_list_shows_returns_paginated_items(): void
    {
        Show::create([
            'id_integration' => 1,
            'name' => 'Dark',
            'type' => 'Scripted',
            'language' => 'German',
            'status' => 'Ended',
            'runtime' => 60,
            'average_runtime' => 55,
            'official_site' => 'https://example.com/dark',
            'rating' => 8.70,
            'summary' => 'Dark summary',
        ]);

        Show::create([
            'id_integration' => 2,
            'name' => 'Daredevil',
            'type' => 'Scripted',
            'language' => 'English',
            'status' => 'Running',
            'runtime' => 50,
            'average_runtime' => 50,
            'official_site' => 'https://example.com/daredevil',
            'rating' => 8.10,
            'summary' => 'Daredevil summary',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->getJson('/api/shows?name=Dar&page=0&size=1&sortField=name&sortOrder=ASC');

        $response->assertStatus(200)
            ->assertJsonPath('total', 2)
            ->assertJsonPath('page', 0)
            ->assertJsonPath('size', 1)
            ->assertJsonPath('items.0.name', 'Daredevil');
    }

    private function externalShowData(): ExternalShowDTO
    {
        return new ExternalShowDTO(
            integrationId: 100,
            name: 'Dark',
            type: 'Scripted',
            language: 'German',
            status: 'Ended',
            runtime: 60,
            averageRuntime: 58,
            officialSite: 'https://example.com/dark',
            rating: 8.7,
            summary: 'Dark summary',
            episodes: [
                new ExternalEpisodeDTO(
                    integrationId: 1001,
                    name: 'Secrets',
                    season: 1,
                    number: 1,
                    type: 'regular',
                    airdate: '2017-12-01',
                    airtime: '21:00',
                    airstamp: '2017-12-01T21:00:00+00:00',
                    runtime: 52,
                    rating: 8.5,
                    summary: 'Episode 1',
                ),
                new ExternalEpisodeDTO(
                    integrationId: 1002,
                    name: 'Lies',
                    season: 1,
                    number: 2,
                    type: 'regular',
                    airdate: '2017-12-01',
                    airtime: '21:00',
                    airstamp: '2017-12-01T21:00:00+00:00',
                    runtime: 48,
                    rating: null,
                    summary: 'Episode 2',
                ),
            ],
        );
    }
}
