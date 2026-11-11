<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar la caché de permisos de Spatie[cite: 2]
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = [
            /* PERMISOS DEL MODELO DE USUARIOS */
            ['name' => 'view_users', 'description' => 'Ver lista de usuarios.'],
            ['name' => 'create_user', 'description' => 'Crear usuario'],
            ['name' => 'update_user', 'description' => 'Editar usuario'],
            ['name' => 'assign_roles', 'description' => 'Asignar Rol'],
            ['name' => 'assign_permissions_to_users', 'description' => 'Asignar Permisos al usuario'],
            ['name' => 'resert_password', 'description' => 'Resetear Contraseña del usuario'],
            //['name' => 'revoke_user_session', 'description' => 'Remover sesión de usuario'],
            /* PERMISOS DE MODELO DE ROLES*/
            ['name' => 'view_roles', 'description' => 'Ver roles'],
            ['name' => 'create_role', 'description' => 'Crear roles'],
            ['name' => 'show_role', 'description' => 'Detalle del rol'],
            ['name' => 'update_role', 'description' => 'Editar rol'],
            ['name' => 'assing_permissions_rol', 'description' => 'Asignar permisos al rol'],
            /*PERMISOS DE MODELO DE PERMISOS */
            ['name' => 'view_permissions', 'description' => 'Ver permisos'],
            /*PERMISOS DE MODELO DE TIME TOKEN */
            ['name' => 'view_time_token', 'description' => 'Ver tiempo de expiración de token'],
            ['name' => 'create_time_token', 'description' => 'Crear tiempo de expiración de token'],
            /*PERMISOS DE MODELO DE EXPIRY MONTHS */
            ['name' => 'view_expiry_months', 'description' => 'Ver meses de expiración de clave.'],
            ['name' => 'create_expiry_months', 'description' => 'Crear meses de expiración de clave.'],
        ];

        $countPermission = 0;
        foreach ($permission as $p) {
            Permission::updateOrCreate(
                ['name' => $p['name'], 'guard_name' => 'api'],
                ['description' => $p['description']]
            );
            $countPermission++;
        }

        $this->command->info("------------------------------------------------------");
        $this->command->info("Proceso para registrar permisos finalizado con éxito.");
        $this->command->info("Registro creados: $countPermission");
        $this->command->info("------------------------------------------------------");
        
        $roleDev = Role::firstOrCreate(['name' => 'dev', 'guard_name' => 'api']);
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'api']);


        $roleDev->syncPermissions(Permission::all());
        $roleSuperAdmin->syncPermissions(Permission::all());

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
