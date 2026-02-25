<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Тестирование доступа через abilities
 */
class TaskApiAbilitiesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест доступа к task:read
     */
    public function test_user_with_task_read_can_view_tasks(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('read-token', ['task:read'])->plainTextToken;

        Task::factory()->count(2)->create();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks');

        $response->assertOk()->assertJsonCount(2);
    }

    /**
     * Тест запрета для task:create
     */
    public function test_user_without_task_create_cannot_create_task(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('read-token', ['task:read'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', [
                'title' => 'New Task',
                'description' => 'Desc',
            ]);

        $response->assertForbidden();
    }

    /**
     * Тест успешного создания с task:create
     */
    public function test_user_with_task_create_can_create_task(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('create-token', ['task:create'])->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', [
                'title' => 'New Task',
                'description' => 'Desc',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
    }

    /**
     * Тест обновления и удаления с abilities
     */
    public function test_user_with_update_and_delete_abilities(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('full-token', ['task:update','task:delete'])->plainTextToken;

        $task = Task::factory()->create(['title' => 'Old Title']);

        // update
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('tasks', ['title' => 'Updated Title']);

        // delete
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertOk();
        $this->assertDatabaseCount('tasks', 0);
    }
}
