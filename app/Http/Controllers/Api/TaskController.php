<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Список задач (только с ability task:read)
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json(Task::all());
    }

    /**
     * Создание задачи (только с ability task:create)
     */
    public function store(Request $request): JsonResponse
    {
        $task = Task::create($request->only('title', 'description'));

        return response()->json($task, 201);
    }

    /**
     * Обновление задачи (только с ability task:update)
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $task->update($request->only('title', 'description'));

        return response()->json($task);
    }

    /**
     * Удаление задачи (только с ability task:delete)
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
}
