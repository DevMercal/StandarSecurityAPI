<?php

namespace App\Trait;

use Illuminate\Support\Facades\Auth;

trait AuthorizesApiRequest
{
    /**
     * Valida la autenticación y un permiso específico para la API.
     */
    protected function authorizeApiUser(string $permission)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'No autenticado.'
            ], 401);
        }

        if (!$user->hasPermissionTo($permission, 'api')) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => "No tiene permisos para realizar esta acción ({$permission})."
            ], 403);
        }

        return null; // Todo correcto
    }
}
