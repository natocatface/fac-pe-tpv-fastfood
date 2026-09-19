<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Categoria;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoy = Carbon::today();
        $ayer = Carbon::yesterday();
        $inicioMes = Carbon::now()->startOfMonth();
        $inicioMesAnt = Carbon::now()->subMonth()->startOfMonth();
        $finMesAnt = Carbon::now()->subMonth()->endOfMonth();

        // KPIs principales
        $ventasHoy = Pedido::whereDate('fecha_pedido', $hoy)
            ->where('estado', '!=', 'anulado')->sum('total');
        $ventasAyer = Pedido::whereDate('fecha_pedido', $ayer)
            ->where('estado', '!=', 'anulado')->sum('total');
        $variacionDia = $ventasAyer > 0
            ? (($ventasHoy - $ventasAyer) / $ventasAyer) * 100 : 0;

        $pedidosHoy = Pedido::whereDate('fecha_pedido', $hoy)
            ->where('estado', '!=', 'anulado')->count();
        $tickedMedio = $pedidosHoy > 0 ? $ventasHoy / $pedidosHoy : 0;

        $ventasMes = Pedido::where('fecha_pedido', '>=', $inicioMes)
            ->where('estado', '!=', 'anulado')->sum('total');
        $ventasMesAnt = Pedido::whereBetween('fecha_pedido', [$inicioMesAnt, $finMesAnt])
            ->where('estado', '!=', 'anulado')->sum('total');
        $variacionMes = $ventasMesAnt > 0
            ? (($ventasMes - $ventasMesAnt) / $ventasMesAnt) * 100 : 0;

        $totalClientes = Cliente::where('activo', true)->count();
        $clientesNuevosMes = Cliente::where('created_at', '>=', $inicioMes)->count();

        $totalProductos = Producto::where('activo', true)->count();
        $stockBajo = Producto::where('controla_stock', true)
            ->whereColumn('stock', '<=', 'stock_minimo')->count();

        // Ventas últimos 7 días
        $ventas7Dias = Pedido::select(
                DB::raw('DATE(fecha_pedido) as fecha'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as num')
            )
            ->where('fecha_pedido', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->where('estado', '!=', 'anulado')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        $labels7 = [];
        $data7Total = [];
        $data7Num = [];
        for ($i = 6; $i >= 0; $i--) {
            $f = Carbon::now()->subDays($i);
            $key = $f->toDateString();
            $labels7[] = $f->isoFormat('ddd D MMM');
            $data7Total[] = (float) ($ventas7Dias[$key]->total ?? 0);
            $data7Num[] = (int) ($ventas7Dias[$key]->num ?? 0);
        }

        // Ventas por tipo (último mes)
        $ventasPorTipo = Pedido::select('tipo', DB::raw('SUM(total) as total'))
            ->where('fecha_pedido', '>=', $inicioMes)
            ->where('estado', '!=', 'anulado')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();

        // Top productos del mes
        $topProductos = DB::table('pedido_detalles')
            ->join('pedidos', 'pedidos.id', '=', 'pedido_detalles.pedido_id')
            ->where('pedidos.fecha_pedido', '>=', $inicioMes)
            ->where('pedidos.estado', '!=', 'anulado')
            ->select(
                'pedido_detalles.nombre_producto',
                DB::raw('SUM(pedido_detalles.cantidad) as cantidad'),
                DB::raw('SUM(pedido_detalles.total) as total')
            )
            ->groupBy('pedido_detalles.nombre_producto')
            ->orderByDesc('cantidad')
            ->limit(10)
            ->get();

        // Ventas por hora (hoy)
        $ventasHoraHoy = Pedido::select(
                DB::raw('HOUR(fecha_pedido) as hora'),
                DB::raw('SUM(total) as total')
            )
            ->whereDate('fecha_pedido', $hoy)
            ->where('estado', '!=', 'anulado')
            ->groupBy('hora')
            ->pluck('total', 'hora')
            ->toArray();

        $horasLabels = [];
        $horasData = [];
        for ($h = 8; $h <= 23; $h++) {
            $horasLabels[] = sprintf('%02d:00', $h);
            $horasData[] = (float) ($ventasHoraHoy[$h] ?? 0);
        }

        // Pedidos en curso
        $pedidosEnCurso = Pedido::with(['mesa', 'cliente', 'user'])
            ->whereIn('estado', ['pendiente', 'en_preparacion', 'preparado', 'en_camino'])
            ->orderByDesc('fecha_pedido')
            ->limit(8)
            ->get();

        // Categorías más vendidas
        $ventasCategorias = DB::table('pedido_detalles')
            ->join('pedidos', 'pedidos.id', '=', 'pedido_detalles.pedido_id')
            ->join('productos', 'productos.id', '=', 'pedido_detalles.producto_id')
            ->join('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->where('pedidos.fecha_pedido', '>=', $inicioMes)
            ->where('pedidos.estado', '!=', 'anulado')
            ->select(
                'categorias.nombre',
                'categorias.color',
                DB::raw('SUM(pedido_detalles.total) as total')
            )
            ->groupBy('categorias.id', 'categorias.nombre', 'categorias.color')
            ->orderByDesc('total')
            ->get();

        return view('dashboard.index', compact(
            'ventasHoy', 'ventasAyer', 'variacionDia',
            'pedidosHoy', 'tickedMedio',
            'ventasMes', 'ventasMesAnt', 'variacionMes',
            'totalClientes', 'clientesNuevosMes',
            'totalProductos', 'stockBajo',
            'labels7', 'data7Total', 'data7Num',
            'ventasPorTipo', 'topProductos',
            'horasLabels', 'horasData',
            'pedidosEnCurso', 'ventasCategorias'
        ));
    }
}
