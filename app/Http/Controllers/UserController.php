<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Trait\AuthorizesApiRequest;
use App\Http\Resources\UserResource;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    use AuthorizesApiRequest;

    public function index (){
        if ($authError = $this->authorizeApiUser('view_users')) {
            return $authError;
        }
        try {
            $user = Auth::guard('api')->user();
            $query = User::with(['roles.permissions']);
            if ($user->hasRole('dev')) {
                
            }elseif ($user->hasRole('super_admin')) {
                $query->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'dev');
                });
            }else {
                $query->whereDoesntHave('roles', function ($q) {
                    $q->whereIn('name', ['dev','super_admin']);
                });
            }
            $users = $query->get();
            if ($users->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron registros'
                ], 200);
            }else {
                return response()->json([
                    'users' => UserResource::collection($users)
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Errors Serve',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function store (Request $request) {
        if ($authError = $this->authorizeApiUser('view_users')) {
            return $authError;
        }
        DB::beginTransaction();
        try {
            $emailUser = $request->username . "@mercal.gob.ve";
            $request->merge(['email' => $emailUser]);
            $messages = [
                'cedula.unique' => 'La cedula ingresada ya se encuentra registrada en el sistema.',
                'cedula.digits_between' => 'La cedula debe tener entre 7 y 8 digitos.',
                'username.unique' => 'Este nombre de usuario ya esta registrado',
                'username.regex' => 'El usuario solo puede contener letras minusculas (a-z)',
                'roles.exists' => 'El rol seleccionado no es valido.',
                'expiryMonth.required' => 'Tiempo de expiración de clave requerido.',
                'timeToken.required' => 'Tiempo de expiracion de token requerido.'
            ];
            $validator = Validator::make($request->all(), [
                'cedula' => 'required|integer|digits_between:7,8|unique:users,cedula',
                'name' => 'required|string|max:255',
                'username' => [
                            'required',
                            'string',
                            'regex:/^[a-z]+$/u',
                        ],
                'email' => 'unique:users,email',
                'timeToken' => 'required|integer',
                'role' => 'nullable|string|exists:roles,name', 
                'expiryMonth' => 'required|integer',
            ], $messages);
             if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()
                ], 422);
            }
            $user = User::create([
                'cedula' => $request->cedula,
                'name' => $request->name,
                'username' => $request->username,
                'email' => $emailUser,
                'id_time_token' => $request->timeToken,
                'password' => bcrypt($request->cedula),
                'password_changed_at' => null, 
                'id_expiry_month' => $request->expiryMonth, 
                'is_active' => true
            ]);
            if ($request->has('role') && !empty($request->role)) {
                $user->assignRole($request->role);
            }
            DB::commit();
            $user->load(['roles', 'permissions']);
            return response()->json([
                'message' => 'Usuario creado correctamente',
                'user' => new UserResource($user)
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Errors Serve',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id){
        if ($authError = $this->authorizeApiUser('view_users')) {
            return $authError;
        }
        try {
            $authUser = Auth::guard('api')->user();
            $user = User::with(['roles.permissions', 'timeToken'])->find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'Usurio no encontrado.'
                ], 404);
            }
            if ($authUser->hasRole('dev')) {
                // Permitido
            } 
            // 2. El Super-Admin no puede ver al dev, pero sí al resto
            elseif ($authUser->hasRole('super-Admin')) {
                if ($user->hasRole('dev')) {
                    return response()->json([
                        'message' => 'No tiene jerarquía suficiente para ver este perfil.'
                    ], 403);
                }
            } 
            // 3. SecurityLogical no puede ver ni al dev ni al super-admin, pero sí a los demás
            elseif ($authUser->hasRole('securityLogical')) {
                if ($user->hasAnyRole(['dev', 'super-Admin'])) {
                    return response()->json([
                        'message' => 'No tiene jerarquía suficiente para ver este perfil.'
                    ], 403);
                }
            }else {
                if ($user->hasAnyRole(['dev', 'super-Admin', 'admin'])) {
                    return response()->json([
                        'message' => 'No tiene jerarquía suficiente para ver este perfil.'
                    ], 403);
                }
            }
            return response()->json([
                'user' => new UserResource($user)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Errors Server',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateUserRequest $request, $id){
        if ($authError = $this->authorizeApiUser('update_user')) {
            return $authError;
        }
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'message' => 'Error en encontrar el usuario.'
                ], 404);
            }

            $validatedData = $request->validated();
            
            if (!empty($validatedData['password'])) {
                // Asignamos directamente al objeto para asegurar que se procese
                $user->password = bcrypt($validatedData['password']);
            }
            
           // $user->fill(collect($validatedData)->except('password')->toArray());
           if (!empty($validatedData['password'])) {
                $validatedData['password'] = bcrypt($validatedData['password']);
            } else {
                unset($validatedData['password']);
            }

            $validatedData['id_time_token'] = $request->timeToken;
            $validatedData['id_expiry_month'] = $request->expiryMonth;

            $user->update($validatedData);
            
            if (isset($validatedData['role'])) {
                $user->syncRoles($validatedData['role']);
            }
            $user->load(['roles.permissions', 'timeToken']);
            
            return response()->json([
                'message' => 'Usuario actualizado correctamente',
                'user' => new UserResource($user)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Errors Server',
                'errors' => $e->getMessage()
            ], 500);
        } 
    }

    public function assignRoles(Request $request, $id){
        if ($authError = $this->authorizeApiUser('assign_roles')) {
            return $authError;
        }
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            $request->validate([
                'role' => 'nullable|string|exists:roles,name',
            ]);

            $oldRoles = $user->getRoleNames()->toArray();
            
            $user->syncRoles($request->role);

            //Llamamos a la función LogManual para guardar tambien el historico.
            UserObserver::logManual(
            $user, 
                'roles_updated', 
                ['roles' => $oldRoles], 
                ['roles' => (array)$request->role]
            );

            $user->load('roles');
            
            return response()->json([
                'user' => $user,
                'message' => 'Role asignado correctamente'
            ], 200);
       } catch (Exception $e) {
            return response()->json([
                'message' => 'Errors Server',
                'error' => $e->getMessage()
            ], 500);
       }
    }
}
