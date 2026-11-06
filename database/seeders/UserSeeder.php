<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    
    public function run(): void
    {
        $users = [
            [
                'email' => 'arangel@mercal.gob.ve',
                'name' => 'Arturo Rangel',
                'username' => 'arangel',
                'password' => '22036006',
                'cedula' => '22036006',
                'id_expiry_month' => '3',
                'id_time_token' => '5',
                'is_active' => '1',
                'role' => "super_admin"
            ],
            [
                'email' => 'moicastillo@mercal.gob.ve',
                'name' => 'Moises Castillo',
                'username' => 'moicastillo',
                'password' => '18467449',
                'cedula' => '18467449',
                'id_expiry_month' => '3',
                'id_time_token' => '5',
                'is_active' => '1',
                'role' => "dev"
            ],
            [
                'email' => 'kleinysp@mercal.gob.ve',
                'name' => 'Kleinys Prieto',
                'username' => 'kprieto',
                'password' => '20327830',
                'cedula' => '20327830',
                'id_expiry_month' => '3',
                'id_time_token' => '5',
                'is_active' => '1',
                'role' => "dev"
            ],
            [
                'email' => 'danrangel@mercal.gob.ve',
                'name' => 'Danyerbert Rangel',
                'username' => 'danrangel',
                'password' => 'Djrb*1602*',
                'cedula' => '27047631',
                'id_expiry_month' => '3',
                'id_time_token' => '5',
                'is_active' => '1',
                'role' => "dev"
            ],
        ];
        $countUser = 0;
        foreach ($users as $userData) {
            // Guardamos el rol antes de reasignar la variable
            $roleName = $userData['role'];

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'username' => $userData['username'],
                    'password' => bcrypt($userData['password']), // Solo se aplica al guardar, no interfiere en la búsqueda
                    'cedula' => $userData['cedula'],
                    'id_expiry_month' => $userData['id_expiry_month'],
                    'id_time_token' => $userData['id_time_token'],
                    'is_active' => $userData['is_active'],
                ]
            );

            // Buscamos el rol explícitamente para el guard 'api' y lo asignamos
            $role = Role::findByName($roleName, 'api');
            $user->assignRole($role);
            
            $countUser++;
        }
        $this->command->info('-------------------------------------------------------');
        $this->command->info(' Registro de usuarios creados con exito.');
        $this->command->info(" Registros creados: $countUser");
        $this->command->info('--------------------------------------------------------');
    }
}
