<?php

namespace Database\Seeders;

use App\Models\TimeToken;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeTokenSeeder extends Seeder
{
    public function run(): void
    {
        $timeTokenData = [
            ['time_token' => 5, 'description' => '5 minutos', 'is_active' => 1],
            ['time_token' => 10, 'description' => '10 minutos', 'is_active' => 1],
            ['time_token' => 15, 'description' => '15 minutos', 'is_active' => 1],
            ['time_token' => 30, 'description' => '30 minutos', 'is_active' => 1],
            ['time_token' => 60, 'description' => '60 minutos', 'is_active' => 1],
        ];
        $countTimeToken = 0;
        foreach ($timeTokenData as $data) {
            TimeToken::updateOrCreate([
                'time_token' => $data['time_token'],
                'description' => $data['description'],
                'is_active' => $data['is_active']
            ]);
            $countTimeToken++;
        }
        $this->command->info('-----------------------------------------------------');
        $this->command->info(' Proceso de creaciònde tiempo de token con exito.');
        $this->command->info(" Registros con exito: $countTimeToken");
        $this->command->info('-----------------------------------------------------');
    }
}
