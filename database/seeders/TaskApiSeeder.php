<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TaskApiSeeder extends Seeder
{
    /**
     * Сидеры пользователей, токенов и задач
     */
    public function run(): void
    {
        // Пользователи
        $userRead = User::factory()->create([
            'name' => 'Read Only User',
            'email' => 'read@example.com',
            'password' => Hash::make('password123'),
        ]);
        $userRead->createToken('read-token', ['task:read']);

        $userCreate = User::factory()->create([
            'name' => 'Create User',
            'email' => 'create@example.com',
            'password' => Hash::make('password123'),
        ]);
        $userCreate->createToken('create-token', ['task:create']);

        $userFull = User::factory()->create([
            'name' => 'Full Access User',
            'email' => 'full@example.com',
            'password' => Hash::make('password123'),
        ]);
        $userFull->createToken('full-token', ['task:read','task:create','task:update','task:delete']);

        // Задачи
        Task::factory()->count(5)->create();
    }
}
