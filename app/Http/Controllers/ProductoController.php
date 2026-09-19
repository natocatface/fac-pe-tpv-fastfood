<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $q = Producto::with('categoria');

        if ($buscar = $request->buscar) {
            $q->where(function ($w) use ($buscar) {
                $w->where('nombre', 'like', "%$buscar%")
                  ->orWhere('codigo', 'like', "%$buscar%")
                  ->orWhere('codigo_barras', 'like', "%$buscar%");
            });
        }
        if ($cat = $request->categoria_id) {
            $q->where('categoria_id', $cat);
        }
        if ($request->filled('activo')) {
            $q->where('activo', $request->activo);
        }

        $productos = $q->orderBy('nombre')->paginate(15)->withQueryString();
        $categorias = Categoria::orderBy('nombre')->get();
        return view('productos.index', compact('productos', 'categorias'));
    }

    public function create()
    {
        $categorias = Categoria::activas()->get();
        return view('productos.form', [
            'producto' => new Producto(),
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('productos', 'public');
        }
        $this->castBooleans($datos, $request);
        Producto::create($datos);
        return redirect()->route('productos.index')->with('success', 'Producto creado.');
    }

    public function edit(Producto $producto)
    {
        $categorias = Categoria::activas()->get();
        return view('productos.form', compact('producto', 'categorias'));
    }

    public function update(Request $request, Producto $producto)
    {
        $datos = $this->validar($request, $producto->id);
        if ($request->hasFile('imagen')) {
            if ($producto->imagen) Storage::disk('public')->delete($producto->imagen);
            $datos['imagen'] = $request->file('imagen')->store('productos', 'public');
        }
        $this->castBooleans($datos, $request);
        $producto->update($datos);
        return redirect()->route('productos.index')->with('success', 'Producto actualizado.');
    }

    public function destroy(Producto $producto)
    {
        if ($producto->imagen) Storage::disk('public')->delete($producto->imagen);
        $producto->delete();
        return redirect()->route('productos.index')->with('success', 'Producto eliminado.');
    }

    private function validar(Request $r, ?int $id = null): array
    {
        return $r->validate([
            'categoria_id'    => 'required|exists:categorias,id',
            'codigo'          => "nullable|string|max:50|unique:productos,codigo,{$id}",
            'codigo_barras'   => 'nullable|string|max:50',
            'nombre'          => 'required|string|max:150',
            'descripcion'     => 'nullable|string',
            'ingredientes'    => 'nullable|string',
            'alergenos'       => 'nullable|string',
            'precio'          => 'required|numeric|min:0',
            'precio_costo'    => 'nullable|numeric|min:0',
            'precio_oferta'   => 'nullable|numeric|min:0',
            'iva'             => 'required|numeric|min:0|max:100',
            'imagen'          => 'nullable|image|max:2048',
            'tipo'            => 'required|in:simple,menu,combo,compuesto',
            'stock'           => 'nullable|numeric|min:0',
            'stock_minimo'    => 'nullable|numeric|min:0',
            'unidad_medida'   => 'nullable|string|max:20',
            'tiempo_preparacion' => 'nullable|integer|min:0',
            'calorias'        => 'nullable|integer|min:0',
            'nivel_picante'   => 'nullable|integer|min:0|max:5',
            'orden'           => 'nullable|integer',
        ]);
    }

    private function castBooleans(array &$datos, Request $r): void
    {
        foreach ([
            'controla_stock','es_vegetariano','es_vegano','es_sin_gluten','picante',
            'disponible_local','disponible_domicilio','disponible_recogida',
            'destacado','activo'
        ] as $b) {
            $datos[$b] = $r->boolean($b);
        }
    }
}
