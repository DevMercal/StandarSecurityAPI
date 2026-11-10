<?php

namespace App\Http\Controllers;

use App\Trait\AuthorizesApiRequest;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    use AuthorizesApiRequest;
    public function index(){
        if ($authError = $this->authorizeApiUser('view_permissions')) {
            return $authError;
        }
        try {
            $permissions = Permission::all();
            if ($permissions->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron permisos.'
                ], 200);
            }else {
                return response()->json([
                    'permissions' => $permissions
                ], 200);
            }

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Errors Serve',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
