<?php

namespace Database\Seeders;

use App\Models\ExpiryMonth;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExpiryMonthSeeder extends Seeder
{
    public function run(): void
    {
        $expiryMonthsData = [
            ['months' => 3, 'description' => 'Expiracion de clave 3 meses'],
            ['months' => 6, 'description' => 'Expiración de clave 6 meses'],
            ['months' => 12, 'description' => 'Expiración de clave 12 meses'],
        ];
        $countExpiryMonths  = 0;
        foreach ($expiryMonthsData as $data) {
            ExpiryMonth::updateOrCreate([
                'months' => $data['months'],
                'description' => $data['description']
            ]);
            $countExpiryMonths++;
        }
        $this->command->info('----------------------------------------------------------------');
        $this->command->info('Proceso de creación de Tiempo de Expiración de clave con exito.');
        $this->command->info("Registros con exito: $countExpiryMonths");
        $this->command->info('-----------------------------------------------------------------');
    }
}
