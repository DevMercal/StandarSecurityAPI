<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar la caché de permisos de Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Crear el permiso y guardarlo en una variable
        $permission = Permission::create([
            'name' => 'editar-usuarios', 
            'guard_name' => 'api'
        ]);

        // 2. Crear el rol de administrador
        $adminRole = Role::create([
            'name' => 'admin', 
            'guard_name' => 'api'
        ]);

        // 3. Asignar el objeto de permiso directamente
        $adminRole->givePermissionTo($permission);

        // 4. Crear el rol de cliente
        Role::create([
            'name' => 'client', 
            'guard_name' => 'api'
        ]);
    }
}
