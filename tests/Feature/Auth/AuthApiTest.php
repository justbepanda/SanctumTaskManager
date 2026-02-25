<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Тестирование API авторизации через Sanctum Personal Access Tokens
 */
class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверяет успешную регистрацию пользователя и выдачу токена
     */
    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    /**
     * Проверяет успешный логин и выдачу нового токена
     */
    public function test_user_can_login_and_receive_token(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    /**
     * Проверяет logout текущего токена
     */
    public function test_user_can_logout_current_token(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /**
     * Проверяет logout со всех устройств
     */
    public function test_user_can_logout_from_all_devices(): void
    {
        $user = User::factory()->create();

        $user->createToken('device1');
        $user->createToken('device2');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout-all');

        $response->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /**
     * Проверяет получение списка токенов пользователя
     */
    public function test_user_can_view_their_tokens(): void
    {
        $user = User::factory()->create();

        $token1 = $user->createToken('device1');
        $token2 = $user->createToken('device2');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/tokens');

        $response
            ->assertOk()
            ->assertJsonCount(2);

        $this->assertEquals(2, $user->tokens()->count());
    }

    /**
     * Проверяет удаление конкретного токена
     */
    public function test_user_can_delete_specific_token(): void
    {
        $user = User::factory()->create();

        $token1 = $user->createToken('device1');
        $token2 = $user->createToken('device2');

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/auth/tokens/{$token1->accessToken->id}"
        );

        $response->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}
