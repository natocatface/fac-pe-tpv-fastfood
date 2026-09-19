<?php

namespace Database\Seeders;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadisticasSeeder extends Seeder
{
    public function run(): void
    {
        $config = Configuracion::actual();

        // ============== Productos: contar unidades vendidas ==============
        $vendidos = DB::table('pedido_detalles')
            ->join('pedidos', 'pedidos.id', '=', 'pedido_detalles.pedido_id')
            ->where('pedidos.estado', '!=', 'anulado')
            ->select('pedido_detalles.producto_id', DB::raw('SUM(pedido_detalles.cantidad) as total'))
            ->groupBy('pedido_detalles.producto_id')
            ->pluck('total', 'producto_id');

        foreach ($vendidos as $productoId => $total) {
            Producto::where('id', $productoId)->update(['vendidos' => (int) $total]);
        }

        // ============== Clientes: total_pedidos, total_gastado, ultimo_pedido_at, puntos ==============
        $statsClientes = DB::table('pedidos')
            ->whereNotNull('cliente_id')
            ->where('estado', '!=', 'anulado')
            ->select(
                'cliente_id',
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(total) as total_gastado'),
                DB::raw('MAX(fecha_pedido) as ultimo_pedido')
            )
            ->groupBy('cliente_id')
            ->get();

        foreach ($statsClientes as $s) {
            $puntos = $config->programa_puntos
                ? (int) ($s->total_gastado * $config->puntos_por_euro)
                : 0;

            Cliente::where('id', $s->cliente_id)->update([
                'total_pedidos'    => $s->total_pedidos,
                'total_gastado'    => $s->total_gastado,
                'ultimo_pedido_at' => $s->ultimo_pedido,
                'puntos_fidelidad' => $puntos,
            ]);
        }

        // ============== Cajas: totales por método y ventas ==============
        $cajas = Caja::all();
        foreach ($cajas as $caja) {
            $pagosCaja = DB::table('pedido_pagos')
                ->where('caja_id', $caja->id)
                ->select('metodo', DB::raw('SUM(importe) as total'))
                ->groupBy('metodo')
                ->pluck('total', 'metodo');

            $totalEfectivo      = (float) ($pagosCaja['efectivo'] ?? 0);
            $totalTarjeta       = (float) ($pagosCaja['tarjeta'] ?? 0);
            $totalTransferencia = (float) ($pagosCaja['transferencia'] ?? 0);
            $totalOtros         = 0;
            foreach ($pagosCaja as $m => $t) {
                if (!in_array($m, ['efectivo', 'tarjeta', 'transferencia'])) {
                    $totalOtros += (float) $t;
                }
            }
            $totalVentas = $totalEfectivo + $totalTarjeta + $totalTransferencia + $totalOtros;

            $totalGastos = (float) DB::table('caja_movimientos')
                ->where('caja_id', $caja->id)
                ->where('tipo', 'gasto')
                ->sum('importe');

            $numPedidos = DB::table('pedidos')
                ->where('caja_id', $caja->id)
                ->where('estado', 'cobrado')
                ->count();

            $saldoFinalCalc = (float) $caja->saldo_inicial + $totalEfectivo - $totalGastos;
            $saldoReal = $caja->estado === 'cerrada'
                ? round($saldoFinalCalc + (rand(-200, 200) / 100), 2)  // pequeño descuadre simulado
                : null;
            $descuadre = $saldoReal !== null ? round($saldoReal - $saldoFinalCalc, 2) : 0;

            $caja->update([
                'total_efectivo'         => $totalEfectivo,
                'total_tarjeta'          => $totalTarjeta,
                'total_transferencia'    => $totalTransferencia,
                'total_otros'            => $totalOtros,
                'total_ventas'           => $totalVentas,
                'total_gastos'           => $totalGastos,
                'num_pedidos'            => $numPedidos,
                'saldo_final_calculado'  => $saldoFinalCalc,
                'saldo_final_real'       => $saldoReal,
                'descuadre'              => $descuadre,
            ]);
        }

        $this->command->info("✓ Estadísticas recalculadas: productos, clientes y cajas.");
    }
}
