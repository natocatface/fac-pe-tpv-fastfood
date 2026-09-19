<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            // [nombre, email, password, rol]
            ['Administrador',     'admin@tpv.local',     'admin123',     'admin'],
            ['Carlos Gerente',    'gerente@tpv.local',   'gerente123',   'gerente'],
            ['María Cajera',      'cajero@tpv.local',    'cajero123',    'cajero'],
            ['Lucía Pérez',       'lucia@tpv.local',     'cajero123',    'cajero'],
            ['Pedro Sánchez',     'pedro@tpv.local',     'cajero123',    'cajero'],
            ['Andrea Ramírez',    'andrea@tpv.local',    'cajero123',    'cajero'],
            ['Luigi Cocinero',    'luigi@tpv.local',     'cocinero123',  'cocinero'],
            ['Mario Cocinero',    'mario@tpv.local',     'cocinero123',  'cocinero'],
            ['Juan Repartidor',   'juan@tpv.local',      'reparto123',   'repartidor'],
            ['Diego Repartidor',  'diego@tpv.local',     'reparto123',   'repartidor'],
        ];

        foreach ($usuarios as $i => $u) {
            User::updateOrCreate(['email' => $u[1]], [
                'name'       => $u[0],
                'password'   => Hash::make($u[2]),
                'rol'        => $u[3],
                'activo'     => true,
                'created_at' => Carbon::now()->subDays(rand(30, 200)),
            ]);
        }
    }
}
