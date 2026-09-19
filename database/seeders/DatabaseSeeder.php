<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ConfiguracionSeeder::class,   // 1. Configuración base (empresa, moneda)
            UserSeeder::class,            // 2. Usuarios admin/cajero
            CategoriaSeeder::class,       // 3. 8 categorías (pizzas, hamburguesas, ...)
            ProductoSeeder::class,        // 4. ~38 productos con precios e ingredientes
            ZonaMesaSeeder::class,        // 5. 3 zonas y 18 mesas
            ClienteSeeder::class,         // 6. 12 clientes con cumpleaños
            CajaSeeder::class,            // 7. 10 cajas históricas + 1 abierta hoy
            PedidoSeeder::class,          // 8. ~900 pedidos en últimos 60 días
            EstadisticasSeeder::class,    // 9. Recalcular agregados
        ]);
    }
}
