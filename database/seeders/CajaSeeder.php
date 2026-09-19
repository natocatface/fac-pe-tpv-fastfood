<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CajaSeeder extends Seeder
{
    public function run(): void
    {
        $admin  = User::where('email', 'admin@tpv.local')->first();
        $cajero = User::where('email', 'cajero@tpv.local')->first() ?? $admin;

        // 10 cajas de los últimos 10 días + 1 abierta hoy
        for ($i = 10; $i >= 1; $i--) {
            $fecha = Carbon::today()->subDays($i);
            $apertura = $fecha->copy()->setTime(11, 0);
            $cierre   = $fecha->copy()->setTime(23, 30);
            $saldoIni = 100;

            Caja::create([
                'user_id'                => $i % 2 == 0 ? $admin->id : $cajero->id,
                'fecha_apertura'         => $apertura,
                'fecha_cierre'           => $cierre,
                'saldo_inicial'          => $saldoIni,
                'estado'                 => 'cerrada',
                'observaciones'          => "Caja del {$fecha->format('d/m/Y')}",
                'created_at'             => $apertura,
                'updated_at'             => $cierre,
            ]);
        }

        // Caja abierta hoy
        Caja::create([
            'user_id'        => $admin->id,
            'fecha_apertura' => Carbon::today()->setTime(10, 30),
            'saldo_inicial'  => 100,
            'estado'         => 'abierta',
            'observaciones'  => 'Caja del día actual',
            'created_at'     => Carbon::today()->setTime(10, 30),
        ]);

        // Algunos movimientos de gastos en cajas pasadas
        $cajas = Caja::where('estado', 'cerrada')->get();
        $conceptos = [
            ['Compra de servilletas',     12.50],
            ['Pago repartidor (efectivo)',45.00],
            ['Compra fruta',              28.00],
            ['Cambio de billetes en banco', 0],
            ['Limpieza local',            15.00],
            ['Pago proveedor refrescos',  85.50],
            ['Material desechable',       22.30],
            ['Arreglo cafetera',          35.00],
        ];
        foreach ($cajas as $c) {
            $n = rand(1, 3);
            for ($j = 0; $j < $n; $j++) {
                $cc = $conceptos[array_rand($conceptos)];
                CajaMovimiento::create([
                    'caja_id'  => $c->id,
                    'user_id'  => $c->user_id,
                    'tipo'     => 'gasto',
                    'concepto' => $cc[0],
                    'importe'  => $cc[1],
                    'metodo'   => 'efectivo',
                    'fecha'    => $c->fecha_apertura->copy()->addHours(rand(1, 10)),
                ]);
            }
        }
    }
}
