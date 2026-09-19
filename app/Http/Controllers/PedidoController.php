<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $q = Pedido::with(['cliente', 'mesa', 'user']);

        if ($request->filled('estado')) {
            $q->where('estado', $request->estado);
        }
        if ($request->filled('tipo')) {
            $q->where('tipo', $request->tipo);
        }
        if ($request->filled('desde')) {
            $q->whereDate('fecha_pedido', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $q->whereDate('fecha_pedido', '<=', $request->hasta);
        }
        if ($buscar = $request->buscar) {
            $q->where(function ($w) use ($buscar) {
                $w->where('numero', 'like', "%$buscar%")
                  ->orWhere('cliente_nombre', 'like', "%$buscar%")
                  ->orWhere('cliente_telefono', 'like', "%$buscar%");
            });
        }

        $pedidos = $q->orderByDesc('fecha_pedido')->paginate(20)->withQueryString();
        return view('pedidos.index', compact('pedidos'));
    }

    public function show(Pedido $pedido)
    {
        $pedido->load('detalles', 'pagos', 'cliente', 'mesa', 'user');
        return view('pedidos.show', compact('pedido'));
    }

    public function cambiarEstado(Request $request, Pedido $pedido)
    {
        $request->validate(['estado' => 'required|in:'.implode(',', array_keys(Pedido::ESTADOS))]);
        $pedido->update(['estado' => $request->estado]);
        return back()->with('success', 'Estado actualizado.');
    }
}
