<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $q = Categoria::query()->withCount('productos');

        if ($buscar = $request->buscar) {
            $q->where('nombre', 'like', "%$buscar%");
        }

        $categorias = $q->orderBy('orden')->orderBy('nombre')->paginate(20)->withQueryString();
        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias.form', ['categoria' => new Categoria()]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);
        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('categorias', 'public');
        }
        $datos['activa'] = $request->boolean('activa');
        $datos['mostrar_tpv'] = $request->boolean('mostrar_tpv');
        Categoria::create($datos);
        return redirect()->route('categorias.index')->with('success', 'Categoría creada.');
    }

    public function edit(Categoria $categoria)
    {
        return view('categorias.form', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $datos = $this->validar($request, $categoria->id);
        if ($request->hasFile('imagen')) {
            if ($categoria->imagen) Storage::disk('public')->delete($categoria->imagen);
            $datos['imagen'] = $request->file('imagen')->store('categorias', 'public');
        }
        $datos['activa'] = $request->boolean('activa');
        $datos['mostrar_tpv'] = $request->boolean('mostrar_tpv');
        $categoria->update($datos);
        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar: tiene productos asociados.');
        }
        if ($categoria->imagen) Storage::disk('public')->delete($categoria->imagen);
        $categoria->delete();
        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada.');
    }

    private function validar(Request $r, ?int $id = null): array
    {
        return $r->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'icono'       => 'nullable|string|max:50',
            'color'       => 'required|string|max:7',
            'imagen'      => 'nullable|image|max:2048',
            'orden'       => 'nullable|integer',
        ]);
    }
}
