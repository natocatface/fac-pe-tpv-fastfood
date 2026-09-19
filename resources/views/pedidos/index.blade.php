@extends('layouts.app')
@section('titulo', 'Pedidos')
@section('subtitulo', 'Historial de pedidos del negocio')

@section('contenido')
<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Nº pedido, cliente..."></div>
            <div class="col-md-2"><select name="estado" class="form-control form-control-sm"><option value="">Todos los estados</option>@foreach(\App\Models\Pedido::ESTADOS as $k=>$v)<option value="{{ $k }}" @selected(request('estado')==$k)>{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-2"><select name="tipo" class="form-control form-control-sm"><option value="">Todos los tipos</option>@foreach(\App\Models\Pedido::TIPOS as $k=>$v)<option value="{{ $k }}" @selected(request('tipo')==$k)>{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-2"><input type="date" name="desde" class="form-control form-control-sm" value="{{ request('desde') }}"></div>
            <div class="col-md-2"><input type="date" name="hasta" class="form-control form-control-sm" value="{{ request('hasta') }}"></div>
            <div class="col-md-1"><button class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i></button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table tabla-bonita mb-0">
            <thead><tr><th>Nº</th><th>Fecha</th><th>Tipo</th><th>Cliente / Mesa</th><th>Estado</th><th class="text-right">Total</th><th></th></tr></thead>
            <tbody>
                @forelse($pedidos as $p)
                <tr>
                    <td class="fw-bold">{{ $p->numero }}</td>
                    <td>{{ $p->fecha_pedido->format('d/m/Y H:i') }}</td>
                    <td>{{ \App\Models\Pedido::TIPOS[$p->tipo] ?? $p->tipo }}</td>
                    <td>{{ $p->mesa ? 'Mesa '.$p->mesa->numero : ($p->cliente?->nombre_completo ?? $p->cliente_nombre ?? '—') }}</td>
                    <td><span class="badge bg-{{ $p->colorEstado() }}">{{ \App\Models\Pedido::ESTADOS[$p->estado] }}</span></td>
                    <td class="text-right fw-bold">{{ $config->formatearPrecio($p->total) }}</td>
                    <td><a href="{{ route('pedidos.show',$p) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-receipt fa-3x mb-2"></i><br>Sin pedidos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $pedidos->links() }}</div>
</div>
@endsection
