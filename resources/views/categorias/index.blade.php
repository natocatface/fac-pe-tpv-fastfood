@extends('layouts.app')
@section('titulo', 'Categorías')
@section('subtitulo', 'Organiza tu menú en categorías')

@section('botones')
<a href="{{ route('categorias.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva categoría</a>
@endsection

@section('contenido')
<div class="card">
    <div class="card-header">
        <form method="GET" class="d-flex" style="max-width:400px">
            <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Buscar categoría...">
            <button class="btn btn-sm btn-primary ml-2 ms-2"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table tabla-bonita mb-0">
            <thead><tr>
                <th style="width:60px"></th><th>Nombre</th><th>Color</th>
                <th class="text-center">Productos</th><th class="text-center">Activa</th><th class="text-center">TPV</th>
                <th class="text-center">Orden</th><th></th>
            </tr></thead>
            <tbody>
                @forelse($categorias as $c)
                <tr>
                    <td>
                        @if($c->imagen)<img src="{{ asset('storage/'.$c->imagen) }}" style="width:42px;height:42px;border-radius:8px;object-fit:cover">
                        @elseif($c->icono)<i class="fas fa-{{ $c->icono }} fa-2x" style="color:{{ $c->color }}"></i>
                        @else<i class="fas fa-tag fa-2x" style="color:{{ $c->color }}"></i>@endif
                    </td>
                    <td><strong>{{ $c->nombre }}</strong>@if($c->descripcion)<br><small class="text-muted">{{ Str::limit($c->descripcion,60) }}</small>@endif</td>
                    <td><span class="badge" style="background:{{ $c->color }};color:#fff">{{ $c->color }}</span></td>
                    <td class="text-center"><span class="badge bg-secondary">{{ $c->productos_count }}</span></td>
                    <td class="text-center">@if($c->activa)<i class="fas fa-check-circle text-success"></i>@else<i class="fas fa-times-circle text-muted"></i>@endif</td>
                    <td class="text-center">@if($c->mostrar_tpv)<i class="fas fa-eye text-info"></i>@else<i class="fas fa-eye-slash text-muted"></i>@endif</td>
                    <td class="text-center"><span class="badge bg-light text-dark">{{ $c->orden }}</span></td>
                    <td class="text-right">
                        <a href="{{ route('categorias.edit', $c) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('categorias.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5 text-muted"><i class="far fa-folder-open fa-3x mb-2"></i><br>No hay categorías. <a href="{{ route('categorias.create') }}">Crear la primera</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $categorias->links() }}</div>
</div>
@endsection
