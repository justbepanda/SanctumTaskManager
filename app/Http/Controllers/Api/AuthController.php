<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Регистрация пользователя и выдача токена
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')),
        ]);

        $token = $user->createToken(
            name: 'auth_token',
            abilities: ['*']
        )->plainTextToken;

        return response()->json([
            'token' => $token,
        ], 201);
    }

    /**
     * Логин и выдача токена
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->string('email'))
            ->first();

        if (!$user || !Hash::check($request->string('password'), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken(
            name: 'auth_token',
            abilities: ['*'],
            expiresAt: now()->addDays(7),
        );

        $accessToken = $token->accessToken;

        $device = $request->string('device') ?? $request->userAgent() ?? 'unknown';

        $accessToken->forceFill([
            'device_name' => $device,
            'user_agent' => $request->userAgent(),
        ])->save();

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $accessToken->expires_at,
        ]);
    }

    /**
     * Logout (удаляет текущий токен)
     */
    public function logout(): JsonResponse
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    /**
     * Logout со всех устройств
     */
    public function logoutAll(): JsonResponse
    {
        request()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices',
        ]);
    }

    /**
     * Список своих токенов
     */
    public function tokens(): JsonResponse
    {
        $tokens = request()->user()->tokens()->get()->map(fn($token) => [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at,
            'created_at' => $token->created_at,
        ]);

        return response()->json($tokens);
    }

    /**
     * Удаление конкретного токена
     */
    public function deleteToken(int $id): JsonResponse
    {
        $user = request()->user();

        $token = $user->tokens()->whereKey($id)->first();

        if (!$token) {
            return response()->json([
                'message' => 'Token not found',
            ], 404);
        }

        $token->delete();

        return response()->json([
            'message' => 'Token deleted',
        ]);
    }

    /**
     * Выдаёт mobile токен с ограниченными правами.
     */
    public function issueMobileToken(User $user, string $deviceName): string
    {
        $token = $user->createToken(
            name: "mobile:{$deviceName}",
            abilities: [
                'task:read',
                'task:create',
            ],
            expiresAt: now()->addDays(30),
        );

        return $token->plainTextToken;
    }

    /**
     * Удаляет токен конкретного устройства.
     */
    public function revokeDevice(User $user, int $tokenId): void
    {
        $user->tokens()
            ->where('id', $tokenId)
            ->delete();
    }

    /**
     * Удаляет текущий токен.
     */
    public function logoutCurrentDevice(Request $request): JsonResponse
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json();
    }
}
