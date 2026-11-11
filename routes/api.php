<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
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

    //RUTAS DE MODELO DE ROLES Y PERMISSIONS
    Route::apiResource('roles', RoleController::class);
    Route::get('permissions', [PermissionsController::class, 'index']);
    Route::post('roles/assign-permissions/{id}', [RoleController::class, 'assignPermissions']);

    //RUTAS DE MODELO DE USUARIOS
    Route::apiResource('users', UserController::class);
});
