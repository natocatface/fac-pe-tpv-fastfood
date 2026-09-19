<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            // [nombre, apellidos, telefono, movil, email, dir, num, cp, ciudad, provincia, tipo, fecha_nac]
            ['Juan',     'García López',      '912345001', '611111111', 'juan@email.com',     'Calle Goya',          '14',  '28009', 'Madrid',     'Madrid',    'particular', '1985-05-06'],  // hoy cumpleaños
            ['María',    'Fernández Ruiz',    '932000002', '622222222', 'maria@email.com',    'Avenida Diagonal',    '200', '08018', 'Barcelona',  'Barcelona', 'vip',        '1990-03-15'],
            ['Carlos',   'Martínez Soto',     '954000003', '633333333', 'carlos@email.com',   'Calle Sierpes',       '30',  '41003', 'Sevilla',    'Sevilla',   'particular', '1978-08-22'],
            ['Lucía',    'Pérez Sanz',        '923000004', '644444444', 'lucia@email.com',    'Plaza Mayor',         '1',   '37002', 'Salamanca',  'Salamanca', 'particular', '1992-11-30'],
            ['Restaurante Pizza Express', '', '963000005', '655555555', 'demo@empresa.com',   'Avenida Principal',   '100', '46001', 'Valencia',   'Valencia',  'empresa',    null],
            ['Andrea',   'Sánchez Vega',      '981000006', '666666666', 'andrea@email.com',   'Rúa do Vilar',        '47',  '15705', 'Santiago',   'A Coruña',  'vip',        '1988-05-06'], // hoy cumpleaños
            ['David',    'Rodríguez Núñez',   '976000007', '677777777', 'david@email.com',    'Paseo Independencia', '12',  '50001', 'Zaragoza',   'Zaragoza',  'particular', '1983-07-10'],
            ['Patricia', 'González Pardo',    '948000008', '688888888', 'patricia@email.com', 'Avenida de Carlos III','7',  '31002', 'Pamplona',   'Navarra',   'particular', '1995-09-25'],
            ['Miguel',   'Torres Aguilar',    '957000009', '699999999', 'miguel@email.com',   'Calle Cruz Conde',    '15',  '14001', 'Córdoba',    'Córdoba',   'particular', '1980-02-14'],
            ['Sara',     'Jiménez Ramos',     '925000010', '600101010', 'sara@email.com',     'Plaza de Zocodover', '8',   '45001', 'Toledo',     'Toledo',    'particular', '1993-12-05'],
            ['Café Bar Central', '',          '928000011', '600111111', 'cafebarcentral@email.com', 'Calle Triana',  '88',  '35002', 'Las Palmas', 'Las Palmas','empresa',    null],
            ['Roberto',  'Domínguez Castro',  '941000012', '600121212', 'roberto@email.com',  'Calle Portales',      '23',  '26001', 'Logroño',    'La Rioja',  'vip',        '1975-04-18'],
        ];

        foreach ($clientes as $i => $c) {
            Cliente::updateOrCreate(['movil' => $c[3]], [
                'codigo'            => 'CLI-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'nombre'            => $c[0],
                'apellidos'         => $c[1],
                'telefono'          => $c[2],
                'movil'             => $c[3],
                'email'             => $c[4],
                'direccion'         => $c[5],
                'numero'            => $c[6],
                'codigo_postal'     => $c[7],
                'ciudad'            => $c[8],
                'provincia'         => $c[9],
                'pais'              => 'España',
                'tipo'              => $c[10],
                'fecha_nacimiento'  => $c[11],
                'activo'            => true,
                'acepta_marketing'  => true,
                'created_at'        => Carbon::now()->subDays(rand(15, 180)),
            ]);
        }
    }
}
