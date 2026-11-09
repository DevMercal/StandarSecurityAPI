<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TimeToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    
    public function login(Request $request){
        $loginValue = $request->input('login', $request->input('email'));

        $request->merge(['login' => $loginValue]);

        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errors',
                'Errors' => $validator->errors()
            ], 422);
        }

        $fielType = filter_var($loginValue, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $userCandidate = User::where($fielType, $loginValue)->first();

        $errorResponse = !$userCandidate
        ?  response()->json([
            'message' => 'Usuario no encontrado'
        ], 404)
        : (!$userCandidate->is_active
        ? response()->json([
            'message' => 'Su cuenta se encuentra bloqueada por seguridad. Por favor, comuníquese con Seguridad Lógica'
        ], 403)
        : null);

        if ($errorResponse) return $errorResponse;

        if ($request->password === '12345678') {
            return response()->json([
                'message' => 'Por razones de seguridad, no puede iniciar sesión con la clave pedeterminada.'
            ], 403);
        }

        $throttlekey = Str::transliterate(Str::lower($loginValue) . '|' . $request->ip());

        $credentials = [
            $fielType => $loginValue,
            'password' => $request->password
        ];
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            RateLimiter::hit($throttlekey, 3600);
            if (RateLimiter::attempts($throttlekey)>= 5) {
                $userCandidate->update(['is_active' => false]);
                return response()->json([
                    'error' => 'Cuenta Bloqueada',
                    'message' => ' Ha excedido el límite de 5 intentos fallidos. Cuenta desactivada.'
                ], 403);
            }

            $attempstLeft = 5 - RateLimiter::attempts($throttlekey);
            return response()->json([
                'error' => 'Credenciales invalidas.',
                'message' => "Contraseña incorrecta. Le quedan {$attempstLeft} intentos."
            ], 401);
        }

        RateLimiter::clear($throttlekey);
        $user = Auth::guard('api')->user();

        $mounthsConfigured = (int) ($user->expiryMonth->months ?? 3);
        $lastChange = $user->updated_at;
        $dateExpiration = $lastChange->addMonth($mounthsConfigured);
        $passwordExpired = now()->greaterThan($dateExpiration);

        // Usa filter_var para interpretar correctamente "false" como falso
        $checkInitialPassword = filter_var(config('auth.check_initial_password', true), FILTER_VALIDATE_BOOLEAN);

        $esClaveInicial = $checkInitialPassword && Hash::check($user->cedula, $user->password);

        $mustChangePassword = $esClaveInicial || $passwordExpired;

        // 6. Manejo de Cambio de Contraseña Obligatorio
        if ($mustChangePassword) {
            $reason = $esClaveInicial ? 'initial_password' : 'expired_password';

            // NO generamos la sesión activa en la BD ni creamos el token de acceso
            return response()->json([
                'status'  => 'must_change_password',
                'reason'  => $reason,
                'message' => $passwordExpired 
                            ? "Su contraseña ha expirado (Política de cada {$mounthsConfigured} meses)." 
                            : 'Debe cambiar su clave inicial por seguridad.',
                'token'   => null,
                'user'    => [
                    'cedula'       => $user->cedula,
                    'username' => $user->username,
                    'email'    => $user->email,
                ],
            ], 200, [], JSON_UNESCAPED_UNICODE);
        }

        // 7. REGISTRO DE SESIÓN ACTIVA (Login completamente exitoso)
        $token = $this->setTokenTTLAndGenerate($user);

        return response()->json([
            'status'  => 'success',
            'reason'  => null,
            'message' => 'Login exitoso',
            'token'   => $token,
            'user'    => $this->getUserDataWithPermissions($user, $dateExpiration),
        ], 200, [], JSON_UNESCAPED_UNICODE);

    }   

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

    protected function getUserDataWithPermissions($user, $dateExpiration = null)
    {
        if (!$user) return null;

        if (!$dateExpiration) {
            $mesesConfigurados = (int) ($user->expiryMonth?->months ?? 3);
            $lastChange = $user->updated_at ? Carbon::parse($user->updated_at) : now();
            $dateExpiration = $lastChange->addMonths($mesesConfigurados);
        }
        return [
            'id' => $user->id,
            'cedula' => $user->cedula ?? null,
            'username' => $user->username,
            'email' => $user->email,
            'id_token' => $user->id_time_token ?? null,
            'description_token' => $user->timeToken?->description ?? null,
            'roles' => $user->roles()->where('guard_name', 'api')->pluck('name'),
            'permissions' => $user->getAllPermissions()->where('guard_name', 'api')->pluck('name'),
            'password_expries' => $dateExpiration->format('Y-m-d'),
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];
    }

    protected function setTokenTTLAndGenerate($user)
    {
        $tiempoMins = TimeToken::where('id_time_token', $user->id_time_token)->value('time_token') ?? 60;
        auth('api')->factory()->setTTL($tiempoMins);
        return auth('api')->tokenById($user->id);
    }
}