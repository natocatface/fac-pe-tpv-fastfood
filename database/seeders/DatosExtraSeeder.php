<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\CajaMovimiento;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\ClienteDireccion;
use App\Models\ComprobanteElectronico;
use App\Models\ComprobanteLinea;
use App\Models\Configuracion;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\PedidoPago;
use App\Models\Producto;
use App\Models\User;
use App\Models\Zona;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * DatosExtraSeeder
 * -------------------------------------------------------------
 * Añade 10 registros NUEVOS a cada módulo del sistema con
 * fechas variadas (hoy, ayer, esta semana, mes actual, mes
 * anterior, hasta 90 días atrás) para alimentar paneles y
 * gráficos estadísticos del dashboard.
 *
 * Módulos cubiertos:
 *   1) Usuarios            (users)
 *   2) Categorías          (categorias)
 *   3) Productos           (productos)
 *   4) Zonas               (zonas)
 *   5) Mesas               (mesas)
 *   6) Clientes            (clientes)
 *   7) Direcciones cliente (cliente_direcciones)
 *   8) Cajas               (cajas) + movimientos
 *   9) Pedidos             (pedidos + detalles + pagos)
 *  10) Comprobantes        (comprobantes_electronicos + lineas)
 *
 * Ejecutar:
 *   php artisan db:seed --class=DatosExtraSeeder
 * -------------------------------------------------------------
 */
class DatosExtraSeeder extends Seeder
{
    public function run(): void
    {
        $config = Configuracion::actual();

        $this->command->info('► Iniciando DatosExtraSeeder…');

        $this->seedUsuarios();
        $this->seedCategorias();
        $this->seedProductos();
        $this->seedZonas();
        $this->seedMesas();
        $this->seedClientes();
        $this->seedClienteDirecciones();
        $cajas = $this->seedCajas();
        $this->seedCajaMovimientos($cajas);
        $this->seedPedidos($config, $cajas);
        $this->seedComprobantes($config);

        $this->recalcularEstadisticas($config);

        $this->command->info('✓ DatosExtraSeeder completado correctamente.');
    }

    // =========================================================
    // 1) USUARIOS
    // =========================================================
    private function seedUsuarios(): void
    {
        $usuarios = [
            ['Eva Domínguez',     'eva@tpv.local',      'cajero',    85],
            ['Iván Castillo',     'ivan@tpv.local',     'cajero',    70],
            ['Noelia Vargas',     'noelia@tpv.local',   'gerente',   60],
            ['Raúl Méndez',       'raul@tpv.local',     'cocinero',  55],
            ['Sofía Iglesias',    'sofia@tpv.local',    'cocinero',  45],
            ['Hugo Navarro',      'hugo@tpv.local',     'repartidor',40],
            ['Cristina Ortega',   'cristina@tpv.local', 'repartidor',35],
            ['Pablo Reyes',       'pablo@tpv.local',    'cajero',    25],
            ['Beatriz Lara',      'beatriz@tpv.local',  'cajero',    15],
            ['Adrián Cano',       'adrian@tpv.local',   'cocinero',  5],
        ];

        foreach ($usuarios as $u) {
            User::updateOrCreate(
                ['email' => $u[1]],
                [
                    'name'       => $u[0],
                    'password'   => Hash::make('user1234'),
                    'rol'        => $u[2],
                    'activo'     => true,
                    'created_at' => Carbon::now()->subDays($u[3]),
                    'updated_at' => Carbon::now()->subDays($u[3]),
                ]
            );
        }
        $this->command->info('  ✓ 10 usuarios añadidos.');
    }

    // =========================================================
    // 2) CATEGORÍAS
    // =========================================================
    private function seedCategorias(): void
    {
        $categorias = [
            ['Sushi y Asiático',    'fish',           '#0d6efd', 11, 75, 'Sushi, makis y especialidades asiáticas'],
            ['Tacos Mexicanos',     'pepper-hot',     '#ff6b35', 12, 65, 'Auténticos tacos y burritos'],
            ['Wraps y Kebabs',      'drumstick-bite', '#8b4513', 13, 55, 'Wraps de pollo, ternera y vegetales'],
            ['Bowls Saludables',    'bowl-food',      '#16a085', 14, 45, 'Poke bowls y buddha bowls'],
            ['Cafés y Tés',         'mug-hot',        '#795548', 15, 38, 'Cafés especiales, tés e infusiones'],
            ['Desayunos',           'egg',            '#f39c12', 16, 30, 'Desayunos completos y tostadas'],
            ['Veggie & Vegano',     'seedling',       '#27ae60', 17, 22, 'Opciones 100% vegetales'],
            ['Sin Gluten',          'wheat-awn-circle-exclamation', '#9b59b6', 18, 18, 'Productos certificados sin gluten'],
            ['Sopas y Cremas',      'bowl-rice',      '#e67e22', 19, 10, 'Sopas calientes y caldos'],
            ['Catering Eventos',    'champagne-glasses','#c0392b',20,  3, 'Servicio de catering para eventos especiales'],
        ];

        foreach ($categorias as $c) {
            Categoria::updateOrCreate(
                ['nombre' => $c[0]],
                [
                    'slug'        => Str::slug($c[0]).'-'.Str::random(4),
                    'icono'       => $c[1],
                    'color'       => $c[2],
                    'orden'       => $c[3],
                    'descripcion' => $c[5],
                    'activa'      => true,
                    'mostrar_tpv' => true,
                    'created_at'  => Carbon::now()->subDays($c[4]),
                    'updated_at'  => Carbon::now()->subDays($c[4]),
                ]
            );
        }
        $this->command->info('  ✓ 10 categorías añadidas.');
    }

    // =========================================================
    // 3) PRODUCTOS
    // =========================================================
    private function seedProductos(): void
    {
        // Mapea categoría → producto a crear (10 productos en total, repartidos)
        $productos = [
            ['Sushi y Asiático',   'Maki Salmón Avocado',     'Roll de salmón fresco y aguacate (8 piezas)',     9.90,  'Salmón, arroz, aguacate, alga nori, sésamo', 'Pescado, soja, sésamo', false, 70],
            ['Sushi y Asiático',   'Yakisoba de Pollo',       'Fideos salteados con pollo y verduras',           10.50, 'Fideos, pollo, brócoli, zanahoria, soja',     'Gluten, soja',           false, 65],
            ['Tacos Mexicanos',    'Tacos al Pastor (3 uds)', 'Cerdo marinado, piña y cilantro',                 8.90,  'Cerdo, piña, cilantro, cebolla, tortilla',    'Gluten',                 false, 60],
            ['Tacos Mexicanos',    'Burrito Veggie',          'Burrito con frijoles, arroz, aguacate y maíz',    7.90,  'Frijoles, arroz, maíz, aguacate, tortilla',   'Gluten',                 true,  50],
            ['Wraps y Kebabs',     'Wrap César con Pollo',    'Pollo, lechuga, parmesano y salsa césar',         6.90,  'Pollo, lechuga, parmesano, tortilla',         'Gluten, lácteos, huevo', false, 40],
            ['Bowls Saludables',   'Poke Bowl Salmón',        'Salmón, arroz, edamame, mango y aguacate',        11.90, 'Salmón, arroz, edamame, mango, aguacate',     'Pescado, soja',          false, 32],
            ['Bowls Saludables',   'Buddha Bowl Vegano',      'Quinoa, garbanzos, kale y hummus',                10.50, 'Quinoa, garbanzos, kale, hummus, semillas',   'Sésamo',                 true,  28],
            ['Cafés y Tés',        'Café Especial Filtrado',  'Café de especialidad de origen Colombia',          2.80, 'Café 100% arábica',                            'Sin alérgenos',          false, 20],
            ['Desayunos',          'Tostada Aguacate y Huevo','Pan de masa madre con aguacate y huevo poché',     6.50, 'Pan, aguacate, huevo, sal Maldon',             'Gluten, huevo',          false, 12],
            ['Veggie & Vegano',    'Hamburguesa de Lentejas', 'Burger 100% vegetal con queso vegano',             8.90, 'Lentejas, avena, queso vegano, lechuga',       'Gluten, soja',           true,  6],
        ];

        foreach ($productos as $i => $p) {
            $cat = Categoria::where('nombre', $p[0])->first();
            if (!$cat) continue;

            Producto::updateOrCreate(
                ['nombre' => $p[1], 'categoria_id' => $cat->id],
                [
                    'slug'              => Str::slug($p[1]).'-'.Str::random(4),
                    'codigo'            => 'PRD-' . str_pad((string)(1000 + $i), 5, '0', STR_PAD_LEFT),
                    'descripcion'       => $p[2],
                    'precio'            => $p[3],
                    'precio_costo'      => round($p[3] * 0.38, 2),
                    'ingredientes'      => $p[4],
                    'alergenos'         => $p[5],
                    'iva'               => 10.00,
                    'tipo'              => 'simple',
                    'controla_stock'    => true,
                    'stock'             => rand(8, 60),
                    'stock_minimo'      => 5,
                    'tiempo_preparacion'=> rand(5, 20),
                    'es_vegetariano'    => $p[6],
                    'es_vegano'         => str_contains(strtolower($p[1]), 'vegan'),
                    'activo'            => true,
                    'destacado'         => $i < 4,
                    'orden'             => $i,
                    'created_at'        => Carbon::now()->subDays($p[7]),
                    'updated_at'        => Carbon::now()->subDays($p[7]),
                ]
            );
        }
        $this->command->info('  ✓ 10 productos añadidos.');
    }

    // =========================================================
    // 4) ZONAS
    // =========================================================
    private function seedZonas(): void
    {
        $zonas = [
            ['Zona Lounge',         '#9c27b0', 95],
            ['Coctelería',          '#e91e63', 85],
            ['Terraza Premium',     '#00bcd4', 75],
            ['Sala Privada A',      '#4caf50', 65],
            ['Sala Privada B',      '#cddc39', 55],
            ['Zona Familiar',       '#ff9800', 45],
            ['Zona Deportiva',      '#673ab7', 35],
            ['Patio Interior',      '#3f51b5', 25],
            ['Rincón Bohemio',      '#795548', 15],
            ['Barra Show-Cooking',  '#f44336', 5],
        ];

        foreach ($zonas as $i => $z) {
            Zona::updateOrCreate(
                ['nombre' => $z[0]],
                [
                    'color'      => $z[1],
                    'orden'      => 20 + $i,
                    'activa'     => true,
                    'created_at' => Carbon::now()->subDays($z[2]),
                    'updated_at' => Carbon::now()->subDays($z[2]),
                ]
            );
        }
        $this->command->info('  ✓ 10 zonas añadidas.');
    }

    // =========================================================
    // 5) MESAS
    // =========================================================
    private function seedMesas(): void
    {
        // Asignamos 1 mesa a cada una de las nuevas zonas
        $zonas = Zona::orderByDesc('id')->take(10)->get()->values();
        $estados = ['libre', 'libre', 'libre', 'ocupada', 'reservada', 'libre', 'libre', 'cobrando', 'libre', 'libre'];
        $formas  = ['cuadrada', 'redonda', 'rectangular'];

        foreach ($zonas as $i => $zona) {
            Mesa::updateOrCreate(
                ['zona_id' => $zona->id, 'numero' => 'X' . ($i + 1)],
                [
                    'nombre'    => 'Mesa Extra ' . ($i + 1),
                    'capacidad' => [2, 4, 6, 8][array_rand([2,4,6,8])],
                    'estado'    => $estados[$i],
                    'pos_x'     => rand(10, 400),
                    'pos_y'     => rand(10, 400),
                    'forma'     => $formas[array_rand($formas)],
                    'activa'    => true,
                    'created_at'=> Carbon::now()->subDays(90 - $i * 9),
                    'updated_at'=> Carbon::now()->subDays(90 - $i * 9),
                ]
            );
        }
        $this->command->info('  ✓ 10 mesas añadidas.');
    }

    // =========================================================
    // 6) CLIENTES
    // =========================================================
    private function seedClientes(): void
    {
        $clientes = [
            // [nombre, apellidos, telefono, movil, email, dir, num, cp, ciudad, provincia, tipo, fecha_nac, dias_atras]
            ['Elena',    'Vázquez Cortés',  '915001001', '612001001', 'elena.v@email.com',   'Calle Alcalá',        '210', '28028', 'Madrid',     'Madrid',     'vip',        '1989-05-18', 90],
            ['Javier',   'Mendoza Pinto',   '915001002', '612001002', 'jmendoza@email.com',  'Gran Vía',            '50',  '28013', 'Madrid',     'Madrid',     'particular', '1991-02-14', 80],
            ['Inés',     'Herrera Caro',    '935001003', '612001003', 'ines.h@email.com',    'Carrer de Mallorca',  '270', '08008', 'Barcelona',  'Barcelona',  'particular', '1986-09-30', 70],
            ['Tomás',    'Salazar Ríos',    '955001004', '612001004', 'tsalazar@email.com',  'Calle Betis',         '15',  '41010', 'Sevilla',    'Sevilla',    'vip',        '1979-11-11', 60],
            ['Mariana',  'Flores Quintana', '965001005', '612001005', 'mariana@email.com',   'Rambla',              '88',  '03002', 'Alicante',   'Alicante',   'particular', '1994-07-22', 50],
            ['Restaurante Don Pepe', '',    '935001006', '612001006', 'donpepe@empresa.com', 'Av. Meridiana',       '300', '08020', 'Barcelona',  'Barcelona',  'empresa',    null,         40],
            ['Catering Bonjour', '',        '912001007', '612001007', 'bonjour@empresa.com', 'Paseo de la Castellana','45','28046','Madrid',     'Madrid',     'empresa',    null,         30],
            ['Daniela',  'Ortiz Bravo',     '961001008', '612001008', 'daniela.o@email.com', 'Av. del Puerto',      '110', '46023', 'Valencia',   'Valencia',   'particular', '1996-03-08', 20],
            ['Sergio',   'Ramírez Cabrera', '948001009', '612001009', 'sramirez@email.com',  'Av. Pío XII',         '20',  '31008', 'Pamplona',   'Navarra',    'particular', '1982-08-14', 10],
            ['Lorena',   'Cabrera Soler',   '922001010', '612001010', 'lcabrera@email.com',  'Calle Castillo',      '30',  '38002', 'Sta. Cruz',  'Tenerife',   'vip',        '1990-12-02', 3],
        ];

        // Buscar el siguiente código consecutivo
        $maxCodigo = Cliente::max('id') ?: 0;

        foreach ($clientes as $i => $c) {
            Cliente::updateOrCreate(
                ['movil' => $c[3]],
                [
                    'codigo'           => 'CLI-' . str_pad((string)($maxCodigo + $i + 100), 4, '0', STR_PAD_LEFT),
                    'nombre'           => $c[0],
                    'apellidos'        => $c[1],
                    'telefono'         => $c[2],
                    'movil'            => $c[3],
                    'email'            => $c[4],
                    'direccion'        => $c[5],
                    'numero'           => $c[6],
                    'codigo_postal'    => $c[7],
                    'ciudad'           => $c[8],
                    'provincia'        => $c[9],
                    'pais'             => 'España',
                    'tipo'             => $c[10],
                    'fecha_nacimiento' => $c[11],
                    'activo'           => true,
                    'acepta_marketing' => true,
                    'descuento_fijo'   => $c[10] === 'vip' ? 10 : 0,
                    'created_at'       => Carbon::now()->subDays($c[12]),
                    'updated_at'       => Carbon::now()->subDays($c[12]),
                ]
            );
        }
        $this->command->info('  ✓ 10 clientes añadidos.');
    }

    // =========================================================
    // 7) DIRECCIONES ADICIONALES POR CLIENTE
    // =========================================================
    private function seedClienteDirecciones(): void
    {
        // Tomar los 10 clientes recién creados (por código)
        $clientes = Cliente::orderByDesc('id')->take(10)->get();

        $direcciones = [
            ['Casa',     'Calle Mayor',         '15',  '28013', 'Madrid'],
            ['Trabajo',  'Avenida Diagonal',    '450', '08036', 'Barcelona'],
            ['Casa',     'Carrer Pelai',        '12',  '08001', 'Barcelona'],
            ['Oficina',  'Paseo de Gracia',     '90',  '08008', 'Barcelona'],
            ['Familiar', 'Calle Larios',        '4',   '29005', 'Málaga'],
            ['Trabajo',  'Calle Sierpes',       '50',  '41004', 'Sevilla'],
            ['Casa',     'Av. de la Constitución','3', '41001', 'Sevilla'],
            ['Estudio',  'Calle de Atocha',     '100', '28012', 'Madrid'],
            ['Casa',     'Calle del Carmen',    '20',  '46003', 'Valencia'],
            ['Trabajo',  'Rambla de Catalunya', '60',  '08007', 'Barcelona'],
        ];

        foreach ($clientes as $i => $cli) {
            ClienteDireccion::create([
                'cliente_id'     => $cli->id,
                'alias'          => $direcciones[$i][0],
                'direccion'      => $direcciones[$i][1],
                'numero'         => $direcciones[$i][2],
                'codigo_postal'  => $direcciones[$i][3],
                'ciudad'         => $direcciones[$i][4],
                'predeterminada' => $i === 0,
                'created_at'     => Carbon::now()->subDays(rand(1, 60)),
                'updated_at'     => Carbon::now()->subDays(rand(1, 60)),
            ]);
        }
        $this->command->info('  ✓ 10 direcciones de cliente añadidas.');
    }

    // =========================================================
    // 8) CAJAS (10 nuevas) en fechas variadas
    // =========================================================
    private function seedCajas(): array
    {
        $admin  = User::where('email', 'admin@tpv.local')->first();
        $cajero = User::where('email', 'cajero@tpv.local')->first() ?? $admin;

        // Días atrás escalonados: 45, 38, 30, 22, 18, 12, 9, 5, 3, 1
        $diasAtras = [45, 38, 30, 22, 18, 12, 9, 5, 3, 1];

        $creadas = [];
        foreach ($diasAtras as $i => $d) {
            $fecha    = Carbon::today()->subDays($d);
            $apertura = $fecha->copy()->setTime(10, 30);
            $cierre   = $fecha->copy()->setTime(23, 45);

            $caja = Caja::create([
                'user_id'        => $i % 2 === 0 ? $admin->id : $cajero->id,
                'fecha_apertura' => $apertura,
                'fecha_cierre'   => $cierre,
                'saldo_inicial'  => 150.00,
                'estado'         => 'cerrada',
                'observaciones'  => "Caja extra del {$fecha->format('d/m/Y')}",
                'created_at'     => $apertura,
                'updated_at'     => $cierre,
            ]);
            $creadas[$fecha->toDateString()] = $caja;
        }

        $this->command->info('  ✓ 10 cajas históricas añadidas.');
        return $creadas;
    }

    // =========================================================
    // 8.b) MOVIMIENTOS DE CAJA (10 gastos)
    // =========================================================
    private function seedCajaMovimientos(array $cajas): void
    {
        $conceptos = [
            ['Compra de servilletas premium',   18.40, 'efectivo'],
            ['Pago propinas repartidor',         32.00, 'efectivo'],
            ['Reposición productos limpieza',    47.85, 'efectivo'],
            ['Compra de pan a panadería local',  25.60, 'efectivo'],
            ['Mantenimiento horno de pizza',     120.00, 'tarjeta'],
            ['Pago a proveedor de bebidas',      210.50, 'transferencia'],
            ['Reposición de cambio en banco',     50.00, 'efectivo'],
            ['Compra de verduras frescas',       65.30, 'efectivo'],
            ['Pago publicidad redes sociales',   75.00, 'tarjeta'],
            ['Compra de menaje desechable',      38.90, 'efectivo'],
        ];

        $cajasArr = array_values($cajas);
        foreach ($conceptos as $i => $c) {
            $caja = $cajasArr[$i % count($cajasArr)];
            CajaMovimiento::create([
                'caja_id'  => $caja->id,
                'user_id'  => $caja->user_id,
                'tipo'     => 'gasto',
                'concepto' => $c[0],
                'importe'  => $c[1],
                'metodo'   => $c[2],
                'fecha'    => $caja->fecha_apertura->copy()->addHours(rand(1, 11)),
                'created_at' => $caja->fecha_apertura->copy()->addHours(rand(1, 11)),
                'updated_at' => $caja->fecha_apertura->copy()->addHours(rand(1, 11)),
            ]);
        }
        $this->command->info('  ✓ 10 movimientos de caja añadidos.');
    }

    // =========================================================
    // 9) PEDIDOS (10 nuevos) con detalles y pagos
    // =========================================================
    private function seedPedidos(Configuracion $config, array $cajas): void
    {
        $usuariosIds = User::whereIn('rol', ['admin', 'gerente', 'cajero'])
            ->where('activo', true)->pluck('id')->toArray();

        $productos = Producto::where('activo', true)->get()->all();
        $clientes  = Cliente::where('activo', true)->get()->all();
        $mesas     = Mesa::where('activa', true)->get()->all();

        if (empty($productos) || empty($clientes)) {
            $this->command->warn('  ✗ Faltan productos o clientes para crear pedidos.');
            return;
        }

        // 10 pedidos repartidos en fechas relevantes para el dashboard:
        //   hoy, ayer, esta semana, este mes, mes anterior
        $fechasHoras = [
            [Carbon::today()->setTime(13, 15), 'mostrador', 'cobrado'],
            [Carbon::today()->setTime(14, 30), 'mesa',      'en_preparacion'],
            [Carbon::today()->setTime(20, 45), 'domicilio', 'cobrado'],
            [Carbon::yesterday()->setTime(13, 50), 'mostrador', 'cobrado'],
            [Carbon::yesterday()->setTime(21, 10), 'recogida',  'cobrado'],
            [Carbon::now()->subDays(3)->setTime(14, 5),  'telefono',  'cobrado'],
            [Carbon::now()->subDays(6)->setTime(20, 30), 'mesa',      'cobrado'],
            [Carbon::now()->subDays(12)->setTime(13, 40),'domicilio', 'cobrado'],
            [Carbon::now()->subDays(22)->setTime(21, 0), 'mostrador', 'cobrado'],
            [Carbon::now()->subDays(40)->setTime(14, 25),'mesa',      'cobrado'],
        ];

        $metodos = ['efectivo','tarjeta','bizum','transferencia','efectivo','tarjeta','bizum','tarjeta','efectivo','tarjeta'];

        $contador = ($config->proximo_pedido ?: 1);

        foreach ($fechasHoras as $i => $fp) {
            [$fechaHora, $tipo, $estado] = $fp;

            $cliente = $clientes[array_rand($clientes)];
            $mesa    = ($tipo === 'mesa' && !empty($mesas)) ? $mesas[array_rand($mesas)] : null;
            $cajaDia = $cajas[$fechaHora->toDateString()] ?? null;

            // 2-4 productos por pedido
            $numProd = rand(2, 4);
            $subtotalConIva  = 0.0;
            $totalIvaImporte = 0.0;
            $lineas = [];
            $usados = [];
            $intentos = 0;
            while (count($usados) < $numProd && $intentos < 20) {
                $idx = rand(0, count($productos) - 1);
                if (!in_array($idx, $usados, true)) {
                    $usados[] = $idx;
                }
                $intentos++;
            }
            foreach ($usados as $idx) {
                $prod    = $productos[$idx];
                $cant    = rand(1, 3);
                $precio  = (float) $prod->precioActual();
                $tlinea  = $cant * $precio;
                $iva     = (float) $prod->iva;
                $base    = $tlinea / (1 + $iva / 100);
                $ivaImp  = $tlinea - $base;

                $subtotalConIva  += $tlinea;
                $totalIvaImporte += $ivaImp;

                $lineas[] = [
                    'producto_id'     => $prod->id,
                    'nombre_producto' => $prod->nombre,
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'iva_pct'         => $iva,
                    'subtotal'        => round($base, 2),
                    'total'           => round($tlinea, 2),
                ];
            }

            $cosEnvio  = $tipo === 'domicilio' ? (float) $config->coste_envio : 0.0;
            $descuento = ($cliente && (float)$cliente->descuento_fijo > 0)
                ? round($subtotalConIva * ((float)$cliente->descuento_fijo / 100), 2)
                : 0.0;
            $total     = round($subtotalConIva + $cosEnvio - $descuento, 2);

            $numero = $config->serie_pedido . str_pad((string)($contador), 6, '0', STR_PAD_LEFT);
            $contador++;

            $pedido = Pedido::create([
                'caja_id'           => $cajaDia ? $cajaDia->id : null,
                'user_id'           => $usuariosIds[array_rand($usuariosIds)],
                'cliente_id'        => $cliente->id,
                'mesa_id'           => $mesa ? $mesa->id : null,
                'numero'            => $numero,
                'serie'             => $config->serie_pedido,
                'tipo'              => $tipo,
                'estado'            => $estado,
                'fecha_pedido'      => $fechaHora,
                'fecha_cobro'       => $estado === 'cobrado' ? $fechaHora->copy()->addMinutes(rand(20, 45)) : null,
                'cliente_nombre'    => $cliente->nombre_completo ?? trim($cliente->nombre.' '.$cliente->apellidos),
                'cliente_telefono'  => $cliente->telefono ?: $cliente->movil,
                'cliente_direccion' => $tipo === 'domicilio' ? $cliente->direccion : null,
                'subtotal'          => round($subtotalConIva - $totalIvaImporte, 2),
                'descuento'         => $descuento,
                'coste_envio'       => $cosEnvio,
                'total_iva'         => round($totalIvaImporte, 2),
                'total'             => $total,
                'pagado'            => $estado === 'cobrado' ? $total : 0,
                'num_comensales'    => $tipo === 'mesa' ? rand(2, 5) : null,
                'notas'             => $i % 3 === 0 ? 'Pedido especial cliente VIP' : null,
                'created_at'        => $fechaHora,
                'updated_at'        => $fechaHora,
            ]);

            foreach ($lineas as $det) {
                $det['pedido_id']  = $pedido->id;
                $det['created_at'] = $fechaHora;
                $det['updated_at'] = $fechaHora;
                PedidoDetalle::create($det);
            }

            if ($estado === 'cobrado') {
                PedidoPago::create([
                    'pedido_id'  => $pedido->id,
                    'caja_id'    => $cajaDia ? $cajaDia->id : null,
                    'metodo'     => $metodos[$i],
                    'importe'    => $total,
                    'fecha'      => $pedido->fecha_cobro,
                    'created_at' => $pedido->fecha_cobro,
                    'updated_at' => $pedido->fecha_cobro,
                ]);
            }
        }

        $config->update(['proximo_pedido' => $contador]);
        $this->command->info('  ✓ 10 pedidos (con detalles y pagos) añadidos.');
    }

    // =========================================================
    // 10) COMPROBANTES ELECTRÓNICOS (10) con líneas
    // =========================================================
    private function seedComprobantes(Configuracion $config): void
    {
        // Verificar que la migración SUNAT/Perú esté aplicada
        if (!Schema::hasTable('comprobantes_electronicos')
            || !Schema::hasColumn('configuracion', 'facturacion_electronica_pe')) {
            $this->command->warn('  ⚠ Migración SUNAT no aplicada. Saltando módulo de comprobantes.');
            $this->command->warn('    Ejecuta primero: php artisan migrate');
            $this->command->warn('    Después: php artisan db:seed --class=ComprobantesExtraSeeder');
            return;
        }

        // Asegurar que el módulo de facturación esté activado para que aparezca en menús
        if (!$config->facturacion_electronica_pe) {
            $config->update([
                'facturacion_electronica_pe' => true,
                'ruc'                        => $config->ruc ?: '20123456789',
                'igv_porcentaje'             => 18.00,
                'serie_factura_pe'           => $config->serie_factura_pe ?: 'F001',
                'serie_boleta_pe'            => $config->serie_boleta_pe  ?: 'B001',
            ]);
        }

        $clientes = Cliente::orderBy('id')->take(20)->get();
        $usuarios = User::where('activo', true)->pluck('id')->toArray();
        $productos = Producto::where('activo', true)->take(20)->get();

        if ($clientes->isEmpty() || $productos->isEmpty()) {
            $this->command->warn('  ✗ Faltan clientes o productos para crear comprobantes.');
            return;
        }

        // Tipos: 6 boletas, 3 facturas, 1 nota de crédito - en fechas variadas
        $comps = [
            ['03', 'B001', 'aceptado',  Carbon::today()],
            ['03', 'B001', 'aceptado',  Carbon::today()->subDay()],
            ['01', 'F001', 'aceptado',  Carbon::today()->subDays(2)],
            ['03', 'B001', 'enviado',   Carbon::today()->subDays(5)],
            ['01', 'F001', 'aceptado',  Carbon::today()->subDays(8)],
            ['03', 'B001', 'aceptado',  Carbon::today()->subDays(14)],
            ['01', 'F001', 'observado', Carbon::today()->subDays(20)],
            ['03', 'B001', 'aceptado',  Carbon::today()->subDays(28)],
            ['03', 'B001', 'rechazado', Carbon::today()->subDays(40)],
            ['07', 'FC01', 'aceptado',  Carbon::today()->subDays(50)],
        ];

        $numFacturaPe = (int) ($config->proximo_factura_pe ?: 1);
        $numBoletaPe  = (int) ($config->proximo_boleta_pe  ?: 1);
        $numNC        = 1;

        foreach ($comps as $i => $cdef) {
            [$tipo, $serie, $estado, $fecha] = $cdef;
            $cli = $clientes[$i % $clientes->count()];

            if ($tipo === '01') {
                $numero = $numFacturaPe++;
                $tipoDocReceptor = '6';
                $numDoc          = '20' . str_pad((string)rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
                $razon           = $cli->tipo === 'empresa' ? $cli->nombre : 'EMPRESA ' . strtoupper($cli->nombre) . ' SAC';
            } elseif ($tipo === '07') {
                $numero = $numNC++;
                $tipoDocReceptor = '1';
                $numDoc          = (string) rand(10000000, 99999999);
                $razon           = trim($cli->nombre . ' ' . $cli->apellidos);
            } else {
                $numero = $numBoletaPe++;
                $tipoDocReceptor = '1';
                $numDoc          = (string) rand(10000000, 99999999);
                $razon           = trim($cli->nombre . ' ' . $cli->apellidos);
            }

            $numeroCompleto = $serie . '-' . str_pad((string)$numero, 8, '0', STR_PAD_LEFT);

            // 1-3 líneas
            $numLineas = rand(1, 3);
            $totalGrav = 0.0;
            $totalIgv  = 0.0;
            $total     = 0.0;
            $lineasData = [];
            for ($l = 0; $l < $numLineas; $l++) {
                $prod    = $productos->random();
                $cant    = rand(1, 4);
                $precio  = (float) $prod->precio;
                $sub     = $precio / 1.18;
                $igv     = $precio - $sub;
                $valTot  = round($sub * $cant, 2);
                $igvTot  = round($igv * $cant, 2);
                $tot     = round($precio * $cant, 2);

                $totalGrav += $valTot;
                $totalIgv  += $igvTot;
                $total     += $tot;

                $lineasData[] = [
                    'producto_id'      => $prod->id,
                    'orden'            => $l + 1,
                    'codigo_producto'  => $prod->codigo ?: 'P-' . $prod->id,
                    'descripcion'      => $prod->nombre,
                    'unidad_medida'    => 'NIU',
                    'cantidad'         => $cant,
                    'valor_unitario'   => round($sub, 4),
                    'precio_unitario'  => round($precio, 4),
                    'descuento'        => 0,
                    'tipo_afectacion_igv' => '10',
                    'igv'              => $igvTot,
                    'valor_total'      => $valTot,
                    'total'            => $tot,
                ];
            }

            $comp = ComprobanteElectronico::create([
                'pedido_id'             => null,
                'cliente_id'            => $cli->id,
                'user_id'               => $usuarios[array_rand($usuarios)],
                'tipo'                  => $tipo,
                'serie'                 => $serie,
                'numero'                => $numero,
                'numero_completo'       => $numeroCompleto,
                'fecha_emision'         => $fecha->toDateString(),
                'hora_emision'          => '13:' . str_pad((string)rand(0, 59), 2, '0', STR_PAD_LEFT) . ':00',
                'fecha_vencimiento'     => $fecha->copy()->addDays(15)->toDateString(),
                'tipo_doc_receptor'     => $tipoDocReceptor,
                'num_doc_receptor'      => $numDoc,
                'razon_social_receptor' => $razon,
                'direccion_receptor'    => $cli->direccion,
                'email_receptor'        => $cli->email,
                'moneda'                => 'PEN',
                'total_gravado'         => round($totalGrav, 2),
                'igv'                   => round($totalIgv, 2),
                'total'                 => round($total, 2),
                'total_letras'          => 'SON ' . strtoupper(number_format($total, 2, ' CON ', '.')) . ' SOLES',
                'estado'                => $estado,
                'codigo_sunat'          => $estado === 'aceptado' ? '0' : ($estado === 'rechazado' ? '2335' : null),
                'mensaje_sunat'         => $estado === 'aceptado' ? 'La Factura ha sido aceptada' : ($estado === 'observado' ? 'Aceptado con observación' : null),
                'intentos_envio'        => in_array($estado, ['aceptado','observado','enviado','rechazado'], true) ? 1 : 0,
                'enviado_at'            => in_array($estado, ['aceptado','observado','enviado','rechazado'], true) ? $fecha : null,
                'aceptado_at'           => in_array($estado, ['aceptado','observado'], true) ? $fecha : null,
                'created_at'            => $fecha,
                'updated_at'            => $fecha,
            ]);

            foreach ($lineasData as $linea) {
                $linea['comprobante_id'] = $comp->id;
                $linea['created_at']     = $fecha;
                $linea['updated_at']     = $fecha;
                ComprobanteLinea::create($linea);
            }
        }

        $config->update([
            'proximo_factura_pe' => $numFacturaPe,
            'proximo_boleta_pe'  => $numBoletaPe,
        ]);

        $this->command->info('  ✓ 10 comprobantes electrónicos añadidos.');
    }

    // =========================================================
    // RECÁLCULO de estadísticas (productos, clientes, cajas)
    // =========================================================
    private function recalcularEstadisticas(Configuracion $config): void
    {
        // Llamamos al seeder existente que ya consolida totales
        (new EstadisticasSeeder())->setContainer(app())->setCommand($this->command)->run();
    }
}
