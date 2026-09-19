<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $datos = [
            'Pizzas' => [
                ['Margarita', 'Tomate, mozzarella, albahaca', 8.50, 'Tomate, mozzarella, albahaca, aceite', 'Gluten, lactosa', true],
                ['Cuatro Quesos', 'Mozzarella, gorgonzola, parmesano, ricotta', 11.00, '', 'Gluten, lactosa', false],
                ['Barbacoa', 'Salsa BBQ, pollo, bacon, cebolla', 11.50, '', 'Gluten, lactosa', false],
                ['Diavola', 'Salami picante, mozzarella', 10.50, '', 'Gluten, lactosa', false],
                ['Vegetal', 'Pimientos, champiñones, cebolla, aceitunas', 10.00, '', 'Gluten, lactosa', true],
                ['Hawaiana', 'Jamón, piña, mozzarella', 10.50, '', 'Gluten, lactosa', false],
                ['Carbonara', 'Bacon, huevo, nata, parmesano', 11.50, '', 'Gluten, lactosa, huevo', false],
            ],
            'Hamburguesas' => [
                ['Clásica', '150g de carne, lechuga, tomate, queso', 7.50, '', 'Gluten, lactosa', false],
                ['Doble', '300g de carne, doble queso, salsa especial', 9.50, '', 'Gluten, lactosa', false],
                ['BBQ Bacon', '150g, bacon, queso cheddar, salsa BBQ', 8.50, '', 'Gluten, lactosa', false],
                ['Vegetal', 'Hamburguesa de garbanzos y verduras', 7.00, '', 'Gluten', true],
                ['Pollo Crispy', 'Pollo crujiente, lechuga, mayonesa', 7.00, '', 'Gluten, huevo', false],
            ],
            'Bocadillos' => [
                ['Jamón Serrano', 'Jamón serrano, tomate, aceite', 4.50, '', 'Gluten', false],
                ['Calamares', 'Calamares a la romana', 5.00, '', 'Gluten, pescado', false],
                ['Tortilla', 'Tortilla de patata casera', 4.00, '', 'Gluten, huevo', true],
                ['Lomo y Queso', 'Lomo de cerdo a la plancha con queso', 5.50, '', 'Gluten, lactosa', false],
            ],
            'Pastas' => [
                ['Carbonara', 'Spaghetti, bacon, huevo, parmesano', 9.50, '', 'Gluten, lactosa, huevo', false],
                ['Boloñesa', 'Tagliatelle con ragú de ternera', 9.00, '', 'Gluten, lactosa', false],
                ['Pesto', 'Penne con salsa pesto y piñones', 8.50, '', 'Gluten, frutos secos', true],
            ],
            'Ensaladas' => [
                ['César', 'Pollo, parmesano, picatostes, salsa césar', 8.00, '', 'Gluten, lactosa, huevo', false],
                ['Mediterránea', 'Tomate, queso feta, aceitunas, atún', 7.50, '', 'Lactosa, pescado', false],
                ['Vegana', 'Mix de hojas, tomate, aguacate, semillas', 7.00, '', '', true],
            ],
            'Bebidas' => [
                ['Coca-Cola 33cl', '', 2.20, '', '', false],
                ['Coca-Cola Zero 33cl', '', 2.20, '', '', false],
                ['Fanta Naranja 33cl', '', 2.20, '', '', false],
                ['Agua mineral 50cl', '', 1.50, '', '', false],
                ['Cerveza Mahou 33cl', '', 2.80, '', 'Gluten', false],
                ['Vino tinto (copa)', '', 3.50, '', 'Sulfitos', false],
            ],
            'Postres' => [
                ['Tiramisú', 'Receta tradicional italiana', 4.50, '', 'Gluten, lactosa, huevo', false],
                ['Tarta de queso', 'Casera con mermelada de frutos rojos', 4.50, '', 'Lactosa, gluten, huevo', false],
                ['Helado 2 bolas', 'Sabores variados', 3.50, '', 'Lactosa', false],
                ['Brownie con helado', 'Brownie casero con helado de vainilla', 4.50, '', 'Gluten, lactosa, huevo, frutos secos', false],
            ],
            'Acompañamientos' => [
                ['Patatas fritas', 'Ración mediana', 3.50, '', '', true],
                ['Patatas deluxe', 'Con especias', 4.00, '', '', true],
                ['Aros de cebolla', '', 4.00, '', 'Gluten', true],
                ['Nuggets pollo (6 uds)', '', 4.50, '', 'Gluten, huevo', false],
            ],
            'Menús del día' => [
                ['Menú Pizza + Bebida', 'Pizza mediana a elegir + bebida 33cl', 11.50, 'Pizza, bebida', 'Gluten, lactosa', false],
                ['Menú Hamburguesa + Patatas + Bebida', 'Hamburguesa clásica con patatas y refresco', 10.90, 'Hamburguesa, patatas, bebida', 'Gluten, lactosa', false],
                ['Menú Pasta + Postre', 'Plato de pasta + postre del día', 12.00, 'Pasta, postre', 'Gluten, lactosa', false],
                ['Menú Infantil', 'Nuggets + patatas + bebida + regalo sorpresa', 7.50, 'Nuggets, patatas, bebida', 'Gluten, huevo', false],
                ['Menú Ejecutivo', 'Ensalada + plato principal + bebida + café', 14.50, 'Ensalada, principal, bebida, café', 'Gluten, lactosa', false],
            ],
            'Promociones' => [
                ['2x1 en Pizzas Medianas', 'Lleva 2 pizzas medianas al precio de 1 (lun-mié)', 11.00, 'Aplicable de lunes a miércoles', 'Gluten, lactosa', false],
                ['Pack Familiar', '2 pizzas grandes + 4 bebidas + nuggets + postre', 32.90, '', 'Gluten, lactosa, huevo', false],
                ['Combo Pareja', 'Pizza grande + 2 bebidas + postre a compartir', 19.90, '', 'Gluten, lactosa', false],
                ['Happy Hour Bebidas', 'Cualquier bebida a mitad de precio (17h-19h)', 1.10, '', '', false],
                ['Mega Combo Amigos', '3 hamburguesas + 3 bebidas + patatas familiares', 24.90, '', 'Gluten, lactosa', false],
            ],
        ];

        foreach ($datos as $catNombre => $productos) {
            $cat = Categoria::where('nombre', $catNombre)->first();
            if (!$cat) continue;
            foreach ($productos as $i => $p) {
                Producto::updateOrCreate(
                    ['nombre' => $p[0], 'categoria_id' => $cat->id],
                    [
                        'slug'         => Str::slug($p[0]).'-'.Str::random(4),
                        'descripcion'  => $p[1],
                        'precio'       => $p[2],
                        'precio_costo' => round($p[2] * 0.35, 2),
                        'ingredientes' => $p[3],
                        'alergenos'    => $p[4],
                        'es_vegetariano' => $p[5],
                        'iva'          => 10,
                        'tipo'         => 'simple',
                        'activo'       => true,
                        'orden'        => $i,
                        'destacado'    => $i < 2,
                    ]
                );
            }
        }
    }
}
