<?php

use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

/**
 * Логин через session (SPA авторизация)
 */
Route::post('/login', function (Request $request) {

    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Invalid credentials',
        ], 401);
    }

    $request->session()->regenerate();

    return response()->json([
        'message' => 'Logged in',
    ]);
});

/**
 * Выход из session (SPA выход)
 */

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json([
        'message' => 'Logged out',
    ]);
});
