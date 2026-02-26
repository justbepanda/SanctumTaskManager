<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);

        Route::get('tokens', [AuthController::class, 'tokens']);
        Route::delete('tokens/{id}', [AuthController::class, 'deleteToken']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks', [TaskController::class, 'index'])->middleware('ability:task:read');
    Route::post('/tasks', [TaskController::class, 'store'])->middleware('ability:task:create');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->middleware('ability:task:update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->middleware('ability:task:delete');
});

/**
 * Возвращает текущего аутентифицированного пользователя.
 */
Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json([
        'id' => $request->user()->id,
        'name' => $request->user()->name,
        'email' => $request->user()->email,
    ]);
});
