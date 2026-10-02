<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Autenticar usuario y retornar token JWT.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        // Intentamos autenticar y generar el token
        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas.',
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Obtener el usuario autenticado actualmente.
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => Auth::guard('api')->user()
        ]);
    }

    /**
     * Cerrar sesión (Invalidar el token).
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }

    /**
     * Refrescar el token de acceso.
     */
    public function refresh()
    {
        try {
            $newToken = Auth::guard('api')->refresh();
            return $this->respondWithToken($newToken);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'El token no pudo ser refrescado o ya ha expirado.',
            ], 401);
        }
    }

    /**
     * Estructura estándar de respuesta con el token JWT.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60 // Segundos de vida
        ]);
    }
}