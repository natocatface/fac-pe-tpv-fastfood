<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $q = Cliente::query();

        if ($buscar = $request->buscar) {
            $q->where(function ($w) use ($buscar) {
                $w->where('nombre', 'like', "%$buscar%")
                  ->orWhere('apellidos', 'like', "%$buscar%")
                  ->orWhere('telefono', 'like', "%$buscar%")
                  ->orWhere('movil', 'like', "%$buscar%")
                  ->orWhere('email', 'like', "%$buscar%")
                  ->orWhere('nif_cif', 'like', "%$buscar%");
            });
        }
        if ($request->filled('tipo')) {
            $q->where('tipo', $request->tipo);
        }

        $clientes = $q->orderBy('nombre')->paginate(20)->withQueryString();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.form', ['cliente' => new Cliente()]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        $datos['acepta_marketing'] = $request->boolean('acepta_marketing');
        $datos['activo'] = true;
        Cliente::create($datos);
        return redirect()->route('clientes.index')->with('success', 'Cliente creado.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load(['direcciones', 'pedidos' => fn ($q) => $q->orderByDesc('fecha_pedido')->limit(20)]);
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.form', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $datos = $this->validar($request, $cliente->id);
        $datos['acepta_marketing'] = $request->boolean('acepta_marketing');
        $datos['activo'] = $request->boolean('activo');
        $cliente->update($datos);
        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->update(['activo' => false]);
        return redirect()->route('clientes.index')->with('success', 'Cliente desactivado.');
    }

    private function validar(Request $r, ?int $id = null): array
    {
        return $r->validate([
            'codigo'           => "nullable|string|max:30|unique:clientes,codigo,{$id}",
            'nombre'           => 'required|string|max:100',
            'apellidos'        => 'nullable|string|max:150',
            'nif_cif'          => 'nullable|string|max:30',
            'email'            => 'nullable|email|max:150',
            'telefono'         => 'nullable|string|max:30',
            'movil'            => 'nullable|string|max:30',
            'fecha_nacimiento' => 'nullable|date',
            'genero'           => 'nullable|in:M,F,Otro',
            'direccion'        => 'nullable|string|max:250',
            'numero'           => 'nullable|string|max:20',
            'piso'             => 'nullable|string|max:20',
            'puerta'           => 'nullable|string|max:10',
            'codigo_postal'    => 'nullable|string|max:15',
            'ciudad'           => 'nullable|string|max:100',
            'provincia'        => 'nullable|string|max:100',
            'pais'             => 'nullable|string|max:80',
            'referencia_direccion' => 'nullable|string',
            'tipo'             => 'required|in:particular,empresa,vip',
            'notas'            => 'nullable|string',
            'descuento_fijo'   => 'nullable|numeric|min:0|max:100',
        ]);
    }
}
