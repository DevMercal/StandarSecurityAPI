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
                'name' => 'Danyerbert Rangel',
                'email'=> 'danrangel@mercal.gob.ve',
                'password' => '12345678',
                'role' => 'admin'
            ],
            [
                'name' => 'Esther Herrera',
                'email'=> 'eherrera@mercal.gob.ve',
                'password' => '12345678',
                'role' => 'client'
            ]
        ];
        $countUser = 0;
        foreach ($users as $userData) {
            // Guardamos el rol antes de reasignar la variable
            $roleName = $userData['role'];

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']) 
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
