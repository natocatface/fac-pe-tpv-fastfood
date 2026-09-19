<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Mesa;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\PedidoPago;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TpvController extends Controller
{
    public function index()
    {
        $categorias = Categoria::activas()->where('mostrar_tpv', true)->get();
        $productos  = Producto::activos()
            ->orderBy('orden')->orderBy('nombre')
            ->get();
        $mesas = Mesa::with('zona')->where('activa', true)->get();

        return view('tpv.index', compact('categorias', 'productos', 'mesas'));
    }

    public function buscarCliente(Request $request)
    {
        $q = $request->get('q', '');
        $clientes = Cliente::where('activo', true)
            ->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%$q%")
                  ->orWhere('apellidos', 'like', "%$q%")
                  ->orWhere('telefono', 'like', "%$q%")
                  ->orWhere('movil', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%");
            })
            ->limit(10)
            ->get(['id', 'nombre', 'apellidos', 'telefono', 'movil', 'direccion', 'ciudad']);
        return response()->json($clientes);
    }

    public function guardarPedido(Request $request)
    {
        $datos = $request->validate([
            'tipo'         => 'required|in:mesa,mostrador,domicilio,recogida,telefono',
            'mesa_id'      => 'nullable|exists:mesas,id',
            'cliente_id'   => 'nullable|exists:clientes,id',
            'cliente_nombre' => 'nullable|string|max:200',
            'cliente_telefono' => 'nullable|string|max:30',
            'cliente_direccion' => 'nullable|string|max:300',
            'notas'        => 'nullable|string',
            'descuento'    => 'nullable|numeric|min:0',
            'coste_envio'  => 'nullable|numeric|min:0',
            'productos'    => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|numeric|min:0.01',
            'productos.*.precio'   => 'required|numeric|min:0',
            'pagos'        => 'nullable|array',
            'pagos.*.metodo' => 'required_with:pagos|in:efectivo,tarjeta,transferencia,bizum,vale,puntos,otro',
            'pagos.*.importe' => 'required_with:pagos|numeric|min:0',
        ]);

        return DB::transaction(function () use ($datos, $request) {
            $config = Configuracion::actual();
            $numero = $config->serie_pedido . str_pad($config->proximo_pedido, 6, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $totalIva = 0;
            foreach ($datos['productos'] as $item) {
                $sub = $item['cantidad'] * $item['precio'];
                $subtotal += $sub;
            }

            $cosEnvio = $datos['coste_envio'] ?? 0;
            $desc = $datos['descuento'] ?? 0;
            $total = $subtotal + $cosEnvio - $desc;

            $pedido = Pedido::create([
                'user_id'  => auth()->id() ?? 1,
                'cliente_id' => $datos['cliente_id'] ?? null,
                'mesa_id'  => $datos['mesa_id'] ?? null,
                'numero'   => $numero,
                'serie'    => $config->serie_pedido,
                'tipo'     => $datos['tipo'],
                'estado'   => isset($datos['pagos']) && count($datos['pagos']) ? 'cobrado' : 'pendiente',
                'fecha_pedido' => now(),
                'fecha_cobro'  => isset($datos['pagos']) ? now() : null,
                'cliente_nombre'    => $datos['cliente_nombre'] ?? null,
                'cliente_telefono'  => $datos['cliente_telefono'] ?? null,
                'cliente_direccion' => $datos['cliente_direccion'] ?? null,
                'subtotal' => $subtotal,
                'descuento' => $desc,
                'coste_envio' => $cosEnvio,
                'total_iva' => $totalIva,
                'total'    => $total,
                'pagado'   => isset($datos['pagos']) ? array_sum(array_column($datos['pagos'], 'importe')) : 0,
                'notas'    => $datos['notas'] ?? null,
            ]);

            foreach ($datos['productos'] as $item) {
                $producto = Producto::find($item['id']);
                $sub = $item['cantidad'] * $item['precio'];
                PedidoDetalle::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $producto->id,
                    'nombre_producto' => $producto->nombre,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                    'iva_pct' => $producto->iva,
                    'subtotal' => $sub,
                    'total'    => $sub,
                    'observaciones' => $item['notas'] ?? null,
                ]);

                if ($producto->controla_stock) {
                    $producto->decrement('stock', $item['cantidad']);
                }
                $producto->increment('vendidos', $item['cantidad']);
            }

            if (isset($datos['pagos'])) {
                foreach ($datos['pagos'] as $p) {
                    PedidoPago::create([
                        'pedido_id' => $pedido->id,
                        'metodo'    => $p['metodo'],
                        'importe'   => $p['importe'],
                        'fecha'     => now(),
                    ]);
                }
            }

            $config->increment('proximo_pedido');

            // Actualizar mesa
            if ($pedido->mesa_id && $pedido->estado !== 'cobrado') {
                Mesa::find($pedido->mesa_id)->update(['estado' => 'ocupada']);
            } elseif ($pedido->mesa_id && $pedido->estado === 'cobrado') {
                Mesa::find($pedido->mesa_id)->update(['estado' => 'libre']);
            }

            // CRM: actualizar estadísticas del cliente
            if ($pedido->cliente_id) {
                $cliente = Cliente::find($pedido->cliente_id);
                $cliente->increment('total_pedidos');
                $cliente->increment('total_gastado', $total);
                $cliente->update(['ultimo_pedido_at' => now()]);
                if ($config->programa_puntos) {
                    $puntos = (int) ($total * $config->puntos_por_euro);
                    $cliente->increment('puntos_fidelidad', $puntos);
                }
            }

            return response()->json([
                'ok'      => true,
                'pedido'  => $pedido->load('detalles', 'pagos'),
                'mensaje' => "Pedido {$pedido->numero} guardado correctamente.",
            ]);
        });
    }

    public function ticket(Pedido $pedido)
    {
        $pedido->load('detalles', 'pagos', 'cliente', 'mesa');
        return view('tpv.ticket', compact('pedido'));
    }
}
