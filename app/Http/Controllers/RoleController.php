<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleAndPermissionResource;
use App\Trait\AuthorizesApiRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use AuthorizesApiRequest;
    public function index(){

        if ($authError = $this->authorizeApiUser('view_roles')) {
            return $authError;
        }
        try {
            $user = Auth::guard('api')->user();
            $query = Role::with(['permissions']);
            if ($user->hasRole('dev')) {
                
            }elseif ($user->hasRole('super_admin')) {
                $query->where('name', '!=', 'dev');
            }else {
                $query->whereNotIN('name', ['dev', 'super_admin']);
            }
            $roles = $query->get();

            if ($roles->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron registros.'
                ], 200);
            }

            return response()->json([
                'roles' =>  RoleAndPermissionResource::collection($roles)
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Errors Server',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request){
        if ($authError = $this->authorizeApiUser('create_role')) {
            return $authError;
        }
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:roles|max:255',
                'permissions' => 'sometimes|array',
                'permissions.*' => 'exists:permissions,name'
            ]);

            $role = Role::create(['name' => $validated['name'], 'guard_name' => 'api']);

            $requestPermissions = $validated['permissions'] ?? [];

            $role->syncPermissions($requestPermissions);
            $role->load('permissions');
            return response()->json([
                'message' => 'Rol creado correctamente.',
                'role' => new RoleAndPermissionResource($role)
            ],201);
        }catch(ValidationException $e){
            return response()->json([
                'message' => 'Errors Validations',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Errors',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id){
        if ($authError = $this->authorizeApiUser('show_role')) {
            return $authError;
        }
        try {
            $role = Role::with('permissions')->find($id);
            if (!$role) {
                return response()->json([
                    'message' => 'No se encontro el rol.'
                ], 404);
            }
            return response()->json([
                'role' => new RoleAndPermissionResource($role)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Errors Server',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id){
        if ($authError = $this->authorizeApiUser('update_role')) {
            return $authError;
        }
        try {
            $role = Role::find($id);
            if (!$role) {
                return response()->json([
                    'message' => 'No se encontro el rol'
                ], 200);
            }

            $validated = $request->validate([
                'name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('roles', 'name')->ignore($id)
                ],
                'permissions' => 'sometimes|array',
                'permissions.*' => 'exists:permissions,name'
            ]);
            if (isset($validated['permissions'])) {
                $role->name = $validated['name'];
                $role->save();
            }
            if (isset($validated['permissions'])) {
                $permissions = $validated['permissions'];
                $role->syncPermissions($permissions);
            }

            $role->load('permissions');
            return response()->json([
                'message' => 'Rol actualizado correctamente',
                'rol' => new RoleAndPermissionResource($role)
            ], 200);
        } catch(ValidationException $e){
            return response()->json([
                'message' => 'Errors Validations',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Errors Server',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function assignPermissions(Request $request, $id){
        if ($authError = $this->authorizeApiUser('assing_permissions_rol')) {
            return $authError;
        }
        try {
            $role = Role::findOrFail($id);
            if (!$role) {
                return response()->json([
                    'message' => 'No se encontro rol'
                ], 404);
            }
            $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,name'
            ]);
            $role->givePermissionTo($request->permissions);
            $role->load('permissions');
            return response()->json([
                'message' => 'Permisos asignados al rol correctamente',
                'role' => new RoleAndPermissionResource($role)
            ], 200);
        } catch(ValidationException $e){
            return response()->json([
                'message' => 'Errors Validations',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Errors Server',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
