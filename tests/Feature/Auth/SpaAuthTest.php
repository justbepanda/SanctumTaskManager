<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

/**
 * Тестирование SPA авторизации
 */
class SpaAuthTest extends TestCase
{

    /**
     * Sanctum отдает CSRF-cookie
     *
     * @return void
     */
    public function test_csrf_cookie_is_returned()
    {
        $response = $this->get('/sanctum/csrf-cookie');

        $response->assertNoContent();

        $this->assertNotNull($response->cookie('XSRF-TOKEN'));
    }

    /**
     * Проверяет успешный логин через session.
     */
    public function test_user_can_login_with_session()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $this->get('/sanctum/csrf-cookie');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertOk();

        $response->assertJson(['message' => 'Logged in']);

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Проверяет, что гость не может получить доступ к защищённому маршруту.
     */
    public function test_guest_cannot_access_protected_route(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertUnauthorized();
    }

    /**
     * Проверяет logout и уничтожение сессии.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->get('/sanctum/csrf-cookie');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->post('/logout');

        $response->assertOk()
            ->assertJson([
                'message' => 'Logged out',
            ]);

        $this->assertGuest();
    }

    /**
     * Проверяет, что логин с неверными данными возвращает 401.
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $this->get('/sanctum/csrf-cookie');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
    }
}
