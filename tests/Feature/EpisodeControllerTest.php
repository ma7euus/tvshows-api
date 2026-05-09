<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Episode;
use App\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class EpisodeControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $userToken;
    private Show $show;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'username' => 'average-user',
            'password' => bcrypt('password'),
            'role' => Role::USER->value,
            'enabled' => true,
        ]);

        $this->userToken = JWTAuth::fromUser($user);

        $this->show = Show::create([
            'id_integration' => 10,
            'name' => 'The Bear',
            'type' => 'Scripted',
            'language' => 'English',
            'status' => 'Running',
            'runtime' => 30,
            'average_runtime' => 30,
            'official_site' => 'https://example.com/the-bear',
            'rating' => 8.50,
            'summary' => 'The Bear summary',
        ]);
    }

    public function test_average_endpoint_groups_by_season_and_ignores_null_ratings(): void
    {
        Episode::create([
            'id_integration' => 501,
            'show_id' => $this->show->id,
            'name' => 'Episode 1',
            'season' => 1,
            'number' => 1,
            'rating' => 8.00,
        ]);

        Episode::create([
            'id_integration' => 502,
            'show_id' => $this->show->id,
            'name' => 'Episode 2',
            'season' => 1,
            'number' => 2,
            'rating' => null,
        ]);

        Episode::create([
            'id_integration' => 503,
            'show_id' => $this->show->id,
            'name' => 'Episode 3',
            'season' => 2,
            'number' => 1,
            'rating' => null,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->getJson('/api/episodes/average?showId=' . $this->show->id);

        $response->assertStatus(200)
            ->assertJsonPath('showId', $this->show->id)
            ->assertJsonPath('showName', 'The Bear')
            ->assertJsonPath('averages.0.season', 1)
            ->assertJsonPath('averages.0.averageRating', 8)
            ->assertJsonPath('averages.1.season', 2)
            ->assertJsonPath('averages.1.averageRating', 0);
    }

    public function test_average_endpoint_returns_422_when_show_has_no_episodes(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->userToken)
            ->getJson('/api/episodes/average?showId=' . $this->show->id);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'No episodes available for the selected show.');
    }
}
