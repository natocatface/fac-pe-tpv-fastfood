<?php

namespace Database\Seeders;

use App\Models\Mesa;
use App\Models\Zona;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ZonaMesaSeeder extends Seeder
{
    public function run(): void
    {
        $zonas = [
            ['nombre' => 'Salón principal',  'color' => '#28a745', 'mesas' => 6, 'prefijo' => 'M',  'capacidad' => 4],
            ['nombre' => 'Terraza',          'color' => '#17a2b8', 'mesas' => 4, 'prefijo' => 'T',  'capacidad' => 4],
            ['nombre' => 'Barra',            'color' => '#ffc107', 'mesas' => 4, 'prefijo' => 'B',  'capacidad' => 2],
            ['nombre' => 'Reservados',       'color' => '#6f42c1', 'mesas' => 3, 'prefijo' => 'R',  'capacidad' => 6],
            ['nombre' => 'Planta superior',  'color' => '#fd7e14', 'mesas' => 4, 'prefijo' => 'P',  'capacidad' => 4],
            ['nombre' => 'Jardín',           'color' => '#20c997', 'mesas' => 4, 'prefijo' => 'J',  'capacidad' => 4],
            ['nombre' => 'VIP',              'color' => '#dc3545', 'mesas' => 2, 'prefijo' => 'V',  'capacidad' => 8],
            ['nombre' => 'Eventos',          'color' => '#e83e8c', 'mesas' => 2, 'prefijo' => 'E',  'capacidad' => 10],
            ['nombre' => 'Take Away',        'color' => '#343a40', 'mesas' => 1, 'prefijo' => 'TA', 'capacidad' => 1],
            ['nombre' => 'Delivery',         'color' => '#007bff', 'mesas' => 1, 'prefijo' => 'D',  'capacidad' => 1],
        ];

        foreach ($zonas as $i => $z) {
            $zona = Zona::updateOrCreate(['nombre' => $z['nombre']], [
                'color'      => $z['color'],
                'orden'      => $i + 1,
                'activa'     => true,
                'created_at' => Carbon::now()->subDays(rand(60, 200)),
            ]);

            for ($n = 1; $n <= $z['mesas']; $n++) {
                Mesa::updateOrCreate(
                    ['zona_id' => $zona->id, 'numero' => $z['prefijo'] . $n],
                    [
                        'capacidad' => $z['capacidad'],
                        'estado'    => 'libre',
                        'activa'    => true,
                        'created_at'=> Carbon::now()->subDays(rand(30, 180)),
                    ]
                );
            }
        }
    }
}
