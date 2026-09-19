@extends('layouts.app')
@section('titulo', 'Productos')
@section('subtitulo', 'Gestión del menú')

@section('botones')
<a href="{{ route('productos.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo producto</a>
@endsection

@section('contenido')
<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-2">
            <div class="col-md-4"><input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Buscar por nombre, código..."></div>
            <div class="col-md-3">
                <select name="categoria_id" class="form-control form-control-sm">
                    <option value="">Todas las categorías</option>
                    @foreach($categorias as $c)<option value="{{ $c->id }}" @selected(request('categoria_id')==$c->id)>{{ $c->nombre }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="activo" class="form-control form-control-sm">
                    <option value="">Todos los estados</option>
                    <option value="1" @selected(request('activo')==='1')>Activos</option>
                    <option value="0" @selected(request('activo')==='0')>Inactivos</option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-sm btn-primary w-100"><i class="fas fa-filter"></i> Filtrar</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table tabla-bonita mb-0">
            <thead><tr>
                <th style="width:65px"></th><th>Producto</th><th>Categoría</th>
                <th class="text-right">Precio</th><th class="text-center">Stock</th>
                <th class="text-center">Estado</th><th class="text-center">Vendidos</th><th></th>
            </tr></thead>
            <tbody>
                @forelse($productos as $p)
                <tr>
                    <td>
                        @if($p->imagen)
                            <img src="{{ $p->imagenUrl() }}"
                                 alt="{{ $p->nombre }}"
                                 loading="lazy"
                                 style="width:48px;height:48px;border-radius:8px;object-fit:cover;background:#f1f3f5"
                                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <span class="prod-thumb-fallback" style="display:none;width:48px;height:48px;border-radius:8px;background:linear-gradient(135deg,#f8f9fa,#e9ecef);align-items:center;justify-content:center;color:{{ $p->categoria->color ?? '#6c757d' }};">
                                <i class="fas fa-{{ $p->categoria->icono ?? 'utensils' }}"></i>
                            </span>
                        @else
                            <span class="prod-thumb-fallback" style="display:inline-flex;width:48px;height:48px;border-radius:8px;background:linear-gradient(135deg,#f8f9fa,#e9ecef);align-items:center;justify-content:center;color:{{ $p->categoria->color ?? '#6c757d' }};">
                                <i class="fas fa-{{ $p->categoria->icono ?? 'utensils' }}"></i>
                            </span>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $p->nombre }}</strong>
                        @if($p->destacado)<span class="badge bg-warning text-dark ml-1 ms-1"><i class="fas fa-star"></i></span>@endif
                        @if($p->precio_oferta)<span class="badge bg-danger ml-1 ms-1">OFERTA</span>@endif
                        <div>
                            <small class="text-muted">{{ $p->codigo ?? '—' }}</small>
                            @if($p->es_vegetariano)<i class="fas fa-leaf text-success small" title="Vegetariano"></i>@endif
                            @if($p->es_vegano)<i class="fas fa-seedling text-success small" title="Vegano"></i>@endif
                            @if($p->es_sin_gluten)<i class="fas fa-bread-slice text-warning small" title="Sin gluten"></i>@endif
                            @if($p->picante)<i class="fas fa-pepper-hot text-danger small" title="Picante"></i>@endif
                        </div>
                    </td>
                    <td><span class="badge" style="background:{{ $p->categoria->color }};color:#fff">{{ $p->categoria->nombre }}</span></td>
                    <td class="text-right">
                        @if($p->precio_oferta)
                            <s class="text-muted small">{{ $config->formatearPrecio($p->precio) }}</s><br>
                            <strong class="text-danger">{{ $config->formatearPrecio($p->precio_oferta) }}</strong>
                        @else
                            <strong>{{ $config->formatearPrecio($p->precio) }}</strong>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($p->controla_stock)
                            <span class="badge bg-{{ $p->stockBajo() ? 'danger' : 'success' }}">{{ $p->stock }}</span>
                        @else
                            <span class="badge bg-light text-dark">∞</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($p->activo)<span class="badge bg-success">Activo</span>@else<span class="badge bg-secondary">Inactivo</span>@endif
                    </td>
                    <td class="text-center">{{ $p->vendidos }}</td>
                    <td class="text-right">
                        <a href="{{ route('productos.edit', $p) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('productos.destroy', $p) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-pizza-slice fa-3x mb-2"></i><br>No hay productos. <a href="{{ route('productos.create') }}">Crear el primero</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $productos->links() }}</div>
</div>
@endsection
