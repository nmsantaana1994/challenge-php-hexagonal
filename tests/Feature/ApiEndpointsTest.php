<?php

namespace Tests\Feature;

use App\Application\Contracts\GiphyClientInterface;
use App\Application\Contracts\TokenIssuerInterface;
use App\Application\DTOs\Auth\IssuedTokenDto;
use App\Application\DTOs\Gif\GifDataDto;
use App\Application\DTOs\Gif\GifSearchResultDto;
use App\Infrastructure\Persistence\Eloquent\Models\ApiLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Mockery\MockInterface;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_empty_returns_422_json(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_invalid_returns_401(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials.',
            ]);
    }

    public function test_login_valid_returns_200_with_token_and_audit_log(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->mock(TokenIssuerInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('issueForUser')
                ->once()
                ->andReturn(new IssuedTokenDto(
                    accessToken: 'fake-access-token',
                    tokenType: 'Bearer',
                    expiresIn: 1800,
                ));
        });

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.access_token', 'fake-access-token')
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.expires_in', 1800);

        $auditLog = ApiLog::query()->latest('id')->first();

        $this->assertNotNull($auditLog);
        $this->assertSame('POST /api/auth/login', $auditLog->service_name);
        $this->assertSame(200, $auditLog->response_code);
        $this->assertSame('[REDACTED]', $auditLog->request_body['password'] ?? null);
        $this->assertStringContainsString('[REDACTED]', (string) $auditLog->response_body);
    }

    public function test_search_gifs_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/gifs/search?query=cats');

        $response->assertStatus(401);
    }

    public function test_search_gifs_with_token_returns_200(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $this->mock(GiphyClientInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('search')
                ->once()
                ->with('cats', 10, 0)
                ->andReturn(new GifSearchResultDto(
                    items: [
                        new GifDataDto(
                            gifId: 'gif_123',
                            title: 'Cats',
                            url: 'https://giphy.test/cats.gif',
                            rawPayload: ['id' => 'gif_123'],
                        ),
                    ],
                    total: 1,
                    count: 1,
                    offset: 0,
                ));
        });

        $response = $this->getJson('/api/gifs/search?query=cats&limit=10&offset=0');

        $response
            ->assertOk()
            ->assertJsonPath('data.0.id', 'gif_123')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_search_gifs_without_query_returns_422(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->getJson('/api/gifs/search');

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['query']);
    }

    public function test_get_gif_by_id_inexistente_returns_404(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $this->mock(GiphyClientInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('findById')
                ->once()
                ->with('id_inventado_123')
                ->andReturnNull();
        });

        $response = $this->getJson('/api/gifs/id_inventado_123');

        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => 'GIF not found.',
            ]);
    }

    public function test_save_favorite_without_token_returns_401(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/favorites', [
            'user_id' => $user->id,
            'gif_id' => 'gif_123',
            'alias' => 'Mi gif',
        ]);

        $response->assertStatus(401);
    }

    public function test_save_favorite_valid_returns_201(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->postJson('/api/favorites', [
            'user_id' => $user->id,
            'gif_id' => 'gif_123',
            'alias' => 'Mi gif',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.gif_id', 'gif_123')
            ->assertJsonPath('data.alias', 'Mi gif');

        $this->assertDatabaseHas('favorite_gifs', [
            'user_id' => $user->id,
            'gif_id' => 'gif_123',
            'alias' => 'Mi gif',
        ]);

        $this->assertDatabaseHas('api_logs', [
            'service_name' => 'POST /api/favorites',
            'response_code' => 201,
            'user_id' => $user->id,
        ]);
    }
}
