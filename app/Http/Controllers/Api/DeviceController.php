<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Возвращает список активных устройств.
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()
            ->tokens()
            ->latest('last_used_at')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'device_name' => $token->device_name,
                'user_agent' => $token->user_agent,
                'last_used_at' => $token->last_used_at,
                'is_current' => $request->user()->currentAccessToken()?->id === $token->id,
            ]);

        return response()->json($tokens);
    }

    /**
     * Удаляет конкретное устройство.
     */
    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $request->user()
            ->tokens()
            ->where('id', $tokenId)
            ->delete();

        return response()->json([
            'message' => 'Device removed',
        ]);
    }

    /**
     * Удаляет все устройства кроме текущего.
     */
    public function logoutOtherDevices(Request $request): JsonResponse
    {
        $currentId = $request->user()->currentAccessToken()->id;

        $request->user()
            ->tokens()
            ->where('id', '!=', $currentId)
            ->delete();

        return response()->json([
            'message' => 'Other devices logged out',
        ]);
    }
}
