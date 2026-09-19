<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $cats = [
            ['nombre' => 'Pizzas',           'icono' => 'pizza-slice',     'color' => '#dc3545', 'orden' => 1,  'descripcion' => 'Nuestras pizzas artesanales al horno de leña'],
            ['nombre' => 'Hamburguesas',     'icono' => 'hamburger',       'color' => '#ffc107', 'orden' => 2,  'descripcion' => 'Hamburguesas 100% carne fresca'],
            ['nombre' => 'Bocadillos',       'icono' => 'bread-slice',     'color' => '#fd7e14', 'orden' => 3,  'descripcion' => 'Bocadillos calientes y fríos'],
            ['nombre' => 'Pastas',           'icono' => 'utensils',        'color' => '#28a745', 'orden' => 4,  'descripcion' => 'Pasta italiana auténtica'],
            ['nombre' => 'Ensaladas',        'icono' => 'leaf',            'color' => '#20c997', 'orden' => 5,  'descripcion' => 'Ensaladas frescas y saludables'],
            ['nombre' => 'Bebidas',          'icono' => 'glass-whiskey',   'color' => '#17a2b8', 'orden' => 6,  'descripcion' => 'Refrescos, cervezas y agua'],
            ['nombre' => 'Postres',          'icono' => 'ice-cream',       'color' => '#e83e8c', 'orden' => 7,  'descripcion' => 'Postres caseros y helados'],
            ['nombre' => 'Acompañamientos',  'icono' => 'cookie-bite',     'color' => '#6f42c1', 'orden' => 8,  'descripcion' => 'Patatas, aros y guarniciones'],
            ['nombre' => 'Menús del día',    'icono' => 'concierge-bell',  'color' => '#007bff', 'orden' => 9,  'descripcion' => 'Menús completos con descuento'],
            ['nombre' => 'Promociones',      'icono' => 'percent',         'color' => '#dc3545', 'orden' => 10, 'descripcion' => 'Ofertas especiales y combos'],
        ];

        foreach ($cats as $c) {
            Categoria::updateOrCreate(['nombre' => $c['nombre']], array_merge($c, [
                'activa'      => true,
                'mostrar_tpv' => true,
                'created_at'  => Carbon::now()->subDays(rand(60, 200)),
            ]));
        }
    }
}
