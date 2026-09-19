@extends('layouts.app')
@section('titulo', $cliente->nombre.' '.$cliente->apellidos)
@section('subtitulo', 'Ficha de cliente')

@section('botones')
<a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Editar</a>
<a href="{{ route('clientes.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
@endsection

@section('contenido')
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#28a745,#155724);color:#fff;font-size:2.5rem;margin:0 auto;display:flex;align-items:center;justify-content:center;font-weight:600">
                    {{ strtoupper(substr($cliente->nombre,0,1).substr($cliente->apellidos,0,1)) }}
                </div>
                <h4 class="mt-3 mb-0">{{ $cliente->nombre_completo }}</h4>
                <span class="badge bg-{{ $cliente->tipo=='vip'?'warning text-dark':($cliente->tipo=='empresa'?'info':'secondary') }} mt-2">{{ ucfirst($cliente->tipo) }}</span>
                @if($cliente->cumpleHoy())<div class="alert alert-warning mt-3">🎂 ¡Hoy es su cumpleaños!</div>@endif
            </div>
            <ul class="list-group list-group-flush">
                @if($cliente->telefono)<li class="list-group-item"><i class="fas fa-phone text-muted"></i> {{ $cliente->telefono }}</li>@endif
                @if($cliente->movil)<li class="list-group-item"><i class="fas fa-mobile text-muted"></i> {{ $cliente->movil }}</li>@endif
                @if($cliente->email)<li class="list-group-item"><i class="far fa-envelope text-muted"></i> {{ $cliente->email }}</li>@endif
                @if($cliente->direccion)<li class="list-group-item"><i class="fas fa-map-marker-alt text-muted"></i> {{ $cliente->direccion }}, {{ $cliente->ciudad }}</li>@endif
                @if($cliente->fecha_nacimiento)<li class="list-group-item"><i class="fas fa-birthday-cake text-muted"></i> {{ $cliente->fecha_nacimiento->format('d/m/Y') }}</li>@endif
            </ul>
        </div>
    </div>
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-4 mb-3"><div class="kpi-card bg-grad-primary p-3 text-center"><i class="fas fa-shopping-bag fa-2x"></i><h3 class="mt-2 mb-0">{{ $cliente->total_pedidos }}</h3><small>Pedidos</small></div></div>
            <div class="col-md-4 mb-3"><div class="kpi-card bg-grad-success p-3 text-center"><i class="fas fa-euro-sign fa-2x"></i><h3 class="mt-2 mb-0">{{ $config->formatearPrecio($cliente->total_gastado) }}</h3><small>Total gastado</small></div></div>
            <div class="col-md-4 mb-3"><div class="kpi-card bg-grad-warning p-3 text-center"><i class="fas fa-star fa-2x"></i><h3 class="mt-2 mb-0">{{ $cliente->puntos_fidelidad }}</h3><small>Puntos</small></div></div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-history"></i> Últimos pedidos</div>
            <div class="card-body p-0">
                <table class="table tabla-bonita mb-0">
                    <thead><tr><th>Nº</th><th>Fecha</th><th>Tipo</th><th>Estado</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                        @forelse($cliente->pedidos as $p)
                        <tr>
                            <td><a href="{{ route('pedidos.show',$p) }}">{{ $p->numero }}</a></td>
                            <td>{{ $p->fecha_pedido->format('d/m/Y H:i') }}</td>
                            <td>{{ \App\Models\Pedido::TIPOS[$p->tipo] ?? $p->tipo }}</td>
                            <td><span class="badge bg-{{ $p->colorEstado() }}">{{ \App\Models\Pedido::ESTADOS[$p->estado] }}</span></td>
                            <td class="text-right fw-bold">{{ $config->formatearPrecio($p->total) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">Sin pedidos aún</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
