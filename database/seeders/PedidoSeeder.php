<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\PedidoPago;
use App\Models\Producto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PedidoSeeder extends Seeder
{
    public function run(): void
    {
        $config = Configuracion::actual();

        $admin  = User::where('email', 'admin@tpv.local')->first();

        // Usar todos los usuarios que pueden vender (admin, gerente, cajero)
        $usuariosVenta = User::whereIn('rol', ['admin', 'gerente', 'cajero'])
            ->where('activo', true)->pluck('id')->toArray();
        if (empty($usuariosVenta)) {
            $usuariosVenta = [$admin->id];
        }
        $usuariosIds = $usuariosVenta;

        // Convertimos las colecciones a arrays planos para usar array_rand en vez de Collection->random()
        $productos = Producto::where('activo', true)->get()->all();
        $clientes  = Cliente::where('activo', true)->get()->all();
        $mesas     = Mesa::where('activa', true)->get()->all();

        if (empty($productos) || empty($clientes) || empty($mesas)) {
            $this->command->warn("✗ Faltan productos, clientes o mesas. Aborta PedidoSeeder.");
            return;
        }

        // Cajas indexadas por fecha (string YYYY-MM-DD)
        $cajasArr = [];
        foreach (Caja::all() as $caja) {
            $cajasArr[$caja->fecha_apertura->toDateString()] = $caja;
        }

        $tiposDistrib = [
            'mostrador'  => 40,
            'mesa'       => 25,
            'domicilio'  => 18,
            'recogida'   => 10,
            'telefono'   => 7,
        ];

        $metodosPago = [
            'efectivo'      => 50,
            'tarjeta'       => 35,
            'bizum'         => 10,
            'transferencia' => 5,
        ];

        $horasFuertes  = [13, 14, 15, 20, 21, 22];
        $horasNormales = [12, 16, 17, 18, 19, 23];

        $contadorPedido = 1;
        $totalPedidosGenerados = 0;

        // Bucle de días: 60 → 0 (ambos inclusive)
        $diasAtras = 60;
        while ($diasAtras >= 0) {
            $fecha   = Carbon::today()->subDays($diasAtras);
            $esFinde = $fecha->isWeekend();
            $esHoy   = ($diasAtras === 0);

            if ($esHoy) {
                $numPedidos = rand(8, 14);
            } elseif ($esFinde) {
                $numPedidos = rand(18, 28);
            } else {
                $numPedidos = rand(10, 18);
            }

            $cajaDia = $cajasArr[$fecha->toDateString()] ?? null;

            $p = 0;
            while ($p < $numPedidos) {
                // Hora con sesgo a horas pico
                if (rand(1, 100) <= 65) {
                    $hora = $horasFuertes[array_rand($horasFuertes)];
                } else {
                    $hora = $horasNormales[array_rand($horasNormales)];
                }
                $minuto = rand(0, 59);
                $fechaHora = $fecha->copy()->setTime($hora, $minuto);

                // Saltar pedidos futuros
                if ($esHoy && $fechaHora->isFuture()) {
                    $p = $p + 1;
                    continue;
                }

                $tipo = $this->ponderado($tiposDistrib);

                // Cliente asociado
                $clienteAsoc = null;
                if (in_array($tipo, ['domicilio', 'telefono'], true)) {
                    $clienteAsoc = $clientes[array_rand($clientes)];
                } elseif (rand(1, 100) <= 40) {
                    $clienteAsoc = $clientes[array_rand($clientes)];
                }

                $mesaAsoc = ($tipo === 'mesa') ? $mesas[array_rand($mesas)] : null;

                // Productos del pedido
                $numProductos = rand(1, 5);
                $detalles = [];
                $subtotalConIva = 0.0;
                $totalIvaImporte = 0.0;

                $maxIdx = count($productos) - 1;
                $indicesUsados = [];
                $intentos = 0;
                while (count($indicesUsados) < $numProductos && $intentos < 20) {
                    $idx = rand(0, $maxIdx);
                    if (!in_array($idx, $indicesUsados, true)) {
                        $indicesUsados[] = $idx;
                    }
                    $intentos = $intentos + 1;
                }

                foreach ($indicesUsados as $idx) {
                    $prod = $productos[$idx];
                    $cantidad = rand(1, 3);
                    $precio   = (float) $prod->precioActual();
                    $totalLinea = $cantidad * $precio;
                    $iva = (float) $prod->iva;
                    $base = $totalLinea / (1 + $iva / 100);
                    $ivaLinea = $totalLinea - $base;

                    $subtotalConIva  = $subtotalConIva + $totalLinea;
                    $totalIvaImporte = $totalIvaImporte + $ivaLinea;

                    $detalles[] = [
                        'producto_id'      => $prod->id,
                        'nombre_producto'  => $prod->nombre,
                        'cantidad'         => $cantidad,
                        'precio_unitario'  => $precio,
                        'iva_pct'          => $iva,
                        'subtotal'         => round($base, 2),
                        'total'            => round($totalLinea, 2),
                    ];
                }

                $cosEnvio = ($tipo === 'domicilio') ? (float) $config->coste_envio : 0.0;

                $descuento = 0.0;
                if ($clienteAsoc && (float) $clienteAsoc->descuento_fijo > 0) {
                    $descuento = round($subtotalConIva * ((float) $clienteAsoc->descuento_fijo / 100), 2);
                } elseif (rand(1, 100) <= 8) {
                    $descuento = rand(100, 300) / 100;
                }

                $total = round($subtotalConIva + $cosEnvio - $descuento, 2);

                // Estado
                if ($esHoy) {
                    $estado = $this->ponderado([
                        'cobrado'        => 50,
                        'pendiente'      => 15,
                        'en_preparacion' => 15,
                        'preparado'      => 10,
                        'en_camino'      => 7,
                        'entregado'      => 3,
                    ]);
                } else {
                    $estado = (rand(1, 100) <= 95) ? 'cobrado' : 'anulado';
                }

                $numero = $config->serie_pedido . str_pad((string) $contadorPedido, 6, '0', STR_PAD_LEFT);
                $contadorPedido = $contadorPedido + 1;

                $pedido = Pedido::create([
                    'caja_id'           => $cajaDia ? $cajaDia->id : null,
                    'user_id'           => $usuariosIds[array_rand($usuariosIds)],
                    'cliente_id'        => $clienteAsoc ? $clienteAsoc->id : null,
                    'mesa_id'           => $mesaAsoc ? $mesaAsoc->id : null,
                    'numero'            => $numero,
                    'serie'             => $config->serie_pedido,
                    'tipo'              => $tipo,
                    'estado'            => $estado,
                    'fecha_pedido'      => $fechaHora,
                    'fecha_cobro'       => ($estado === 'cobrado') ? $fechaHora->copy()->addMinutes(rand(15, 60)) : null,
                    'cliente_nombre'    => $clienteAsoc ? $clienteAsoc->nombre_completo : null,
                    'cliente_telefono'  => $clienteAsoc ? ($clienteAsoc->telefono ?: $clienteAsoc->movil) : null,
                    'cliente_direccion' => ($tipo === 'domicilio' && $clienteAsoc) ? $clienteAsoc->direccion : null,
                    'subtotal'          => round($subtotalConIva - $totalIvaImporte, 2),
                    'descuento'         => $descuento,
                    'coste_envio'       => $cosEnvio,
                    'total_iva'         => round($totalIvaImporte, 2),
                    'total'             => $total,
                    'pagado'            => ($estado === 'cobrado') ? $total : 0,
                    'num_comensales'    => ($tipo === 'mesa') ? rand(1, 4) : null,
                    'created_at'        => $fechaHora,
                    'updated_at'        => $fechaHora,
                ]);

                foreach ($detalles as $det) {
                    $det['pedido_id']  = $pedido->id;
                    $det['created_at'] = $fechaHora;
                    $det['updated_at'] = $fechaHora;
                    PedidoDetalle::create($det);
                }

                if ($estado === 'cobrado') {
                    PedidoPago::create([
                        'pedido_id'  => $pedido->id,
                        'caja_id'    => $cajaDia ? $cajaDia->id : null,
                        'metodo'     => $this->ponderado($metodosPago),
                        'importe'    => $total,
                        'fecha'      => $pedido->fecha_cobro,
                        'created_at' => $pedido->fecha_cobro,
                        'updated_at' => $pedido->fecha_cobro,
                    ]);
                }

                $totalPedidosGenerados = $totalPedidosGenerados + 1;
                $p = $p + 1;
            }

            $diasAtras = $diasAtras - 1;
        }

        $config->update(['proximo_pedido' => $contadorPedido]);

        $this->command->info("✓ {$totalPedidosGenerados} pedidos generados en los últimos 60 días.");
    }

    /** Selección ponderada por probabilidades. */
    private function ponderado(array $opciones): string
    {
        $total = (int) array_sum($opciones);
        if ($total <= 0) {
            return (string) array_key_first($opciones);
        }
        $r = rand(1, $total);
        $acum = 0;
        foreach ($opciones as $k => $peso) {
            $acum = $acum + (int) $peso;
            if ($r <= $acum) {
                return (string) $k;
            }
        }
        return (string) array_key_first($opciones);
    }
}
