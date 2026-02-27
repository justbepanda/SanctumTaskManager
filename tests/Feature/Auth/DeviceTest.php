<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

/**
 * Тестирование работы роутов получения залогиных устройств
 */
class DeviceTest extends TestCase
{
    /**
     * Тест получения списка
     */
    public function test_current_device_is_correct_when_multiple_tokens_exist(): void
    {
        $user = User::factory()->create();

        $user->createToken('old-device');

        $currentToken = $user->createToken('current-device')->plainTextToken;

        $response = $this
            ->withToken($currentToken)
            ->getJson('/api/devices');

        $response->assertOk();

        $devices = $response->json();

        $this->assertCount(2, $devices);

        $current = collect($devices)->firstWhere('is_current', true);

        $this->assertNotNull($current);
    }

    /**
     * Проверяет удаление конкретного устройства.
     */
    public function test_user_can_delete_specific_device(): void
    {
        $user = User::factory()->create();

        // создаём два устройства
        $token1 = $user->createToken('device-1');
        $token2 = $user->createToken('device-2');

        $plainToken = $token1->plainTextToken;

        $response = $this
            ->withToken($plainToken)
            ->deleteJson("/api/devices/{$token2->accessToken->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token2->accessToken->id,
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token1->accessToken->id,
        ]);
    }

    /**
     * Проверяет, что пользователь не может удалить чужое устройство.
     */
    public function test_user_cannot_delete_foreign_device(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $userToken = $user->createToken('my-device');
        $foreignToken = $anotherUser->createToken('foreign-device');

        $response = $this
            ->withToken($userToken->plainTextToken)
            ->deleteJson("/api/devices/{$foreignToken->accessToken->id}");

        $response->assertOk();

        // Токен другого пользователя должен остаться
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $foreignToken->accessToken->id,
        ]);
    }

    /**
     * Проверяет logout всех устройств кроме текущего.
     */
    public function test_user_can_logout_other_devices(): void
    {
        $user = User::factory()->create();

        // создаём 3 устройства
        $oldToken1 = $user->createToken('device-1');
        $oldToken2 = $user->createToken('device-2');
        $currentToken = $user->createToken('current-device');

        $response = $this
            ->withToken($currentToken->plainTextToken)
            ->deleteJson('/api/devices');

        $response->assertOk();

        // Должен остаться только текущий токен
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $currentToken->accessToken->id,
        ]);
    }

    /**
     * Проверяет, что гость не может управлять устройствами.
     */
    public function test_guest_cannot_access_devices(): void
    {
        $response = $this->getJson('/api/devices');

        $response->assertUnauthorized();
    }
}
