@extends('layouts.app')
@section('titulo', 'Clientes (CRM)')
@section('subtitulo', 'Base de datos de clientes y fidelización')

@section('botones')
<a href="{{ route('clientes.create') }}" class="btn btn-primary"><i class="fas fa-user-plus"></i> Nuevo cliente</a>
@endsection

@section('contenido')
<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-2">
            <div class="col-md-6"><input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Nombre, teléfono, email, NIF..."></div>
            <div class="col-md-3">
                <select name="tipo" class="form-control form-control-sm">
                    <option value="">Todos los tipos</option>
                    <option value="particular" @selected(request('tipo')=='particular')>Particular</option>
                    <option value="empresa" @selected(request('tipo')=='empresa')>Empresa</option>
                    <option value="vip" @selected(request('tipo')=='vip')>VIP</option>
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-sm btn-primary w-100"><i class="fas fa-filter"></i> Filtrar</button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table tabla-bonita mb-0">
            <thead><tr>
                <th>Cliente</th><th>Contacto</th><th>Dirección</th>
                <th class="text-center">Pedidos</th><th class="text-right">Gastado</th>
                <th class="text-center">Puntos</th><th class="text-center">Tipo</th><th></th>
            </tr></thead>
            <tbody>
                @forelse($clientes as $c)
                <tr>
                    <td>
                        <strong>{{ $c->nombre }} {{ $c->apellidos }}</strong>
                        @if($c->cumpleHoy())<span class="badge bg-warning text-dark ms-1">🎂 Cumpleaños</span>@endif
                        <br><small class="text-muted">{{ $c->codigo ?? '—' }} {{ $c->nif_cif }}</small>
                    </td>
                    <td>
                        @if($c->telefono)<i class="fas fa-phone small text-muted"></i> {{ $c->telefono }}<br>@endif
                        @if($c->movil)<i class="fas fa-mobile small text-muted"></i> {{ $c->movil }}<br>@endif
                        @if($c->email)<i class="far fa-envelope small text-muted"></i> {{ $c->email }}@endif
                    </td>
                    <td><small>{{ $c->direccion }}{{ $c->numero ? ', '.$c->numero : '' }}<br>{{ $c->codigo_postal }} {{ $c->ciudad }}</small></td>
                    <td class="text-center"><span class="badge bg-primary">{{ $c->total_pedidos }}</span></td>
                    <td class="text-right fw-bold">{{ $config->formatearPrecio($c->total_gastado) }}</td>
                    <td class="text-center">@if($c->puntos_fidelidad)<span class="badge bg-warning text-dark"><i class="fas fa-star"></i> {{ $c->puntos_fidelidad }}</span>@else—@endif</td>
                    <td class="text-center">
                        @if($c->tipo=='vip')<span class="badge bg-warning text-dark"><i class="fas fa-crown"></i> VIP</span>
                        @elseif($c->tipo=='empresa')<span class="badge bg-info">Empresa</span>
                        @else<span class="badge bg-secondary">Particular</span>@endif
                    </td>
                    <td class="text-right">
                        <a href="{{ route('clientes.show', $c) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('clientes.edit', $c) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-users fa-3x mb-2"></i><br>No hay clientes. <a href="{{ route('clientes.create') }}">Crear el primero</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $clientes->links() }}</div>
</div>
@endsection
