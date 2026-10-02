<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Rutas públicas con rate limiting de seguridad para prevenir ataques de fuerza bruta
Route::middleware('throttle:auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas protegidas por el middleware JWT
Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});


// Rutas protegidas exigiendo un Rol específico de Spatie
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/admin/reports', function () {
        return response()->json([
            'success' => true,
            'message' => 'Reportes confidenciales de administración.'
        ]);
    });
});

// Rutas protegidas exigiendo un Permiso específico de Spatie
Route::middleware(['auth:api', 'permission:editar-usuarios'])->group(function () {
    Route::put('/users/{id}', function ($id) {
        return response()->json([
            'success' => true,
            'message' => "Usuario $id actualizado correctamente."
        ]);
    });
});