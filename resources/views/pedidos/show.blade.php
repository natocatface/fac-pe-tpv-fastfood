@extends('layouts.app')
@section('titulo', 'Pedido '.$pedido->numero)

@section('botones')
@if(($config->facturacion_electronica_pe ?? false) && in_array($pedido->estado, ['cobrado','entregado','preparado']))
    @php $compExistente = \App\Models\ComprobanteElectronico::where('pedido_id', $pedido->id)->first(); @endphp
    @if($compExistente)
        <a href="{{ route('comprobantes.show', $compExistente) }}" class="btn btn-warning"><i class="fas fa-file-invoice-dollar"></i> Ver comprobante {{ $compExistente->numero_completo }}</a>
    @else
        <a href="{{ route('comprobantes.emitir', $pedido) }}" class="btn" style="background:#c8102e;color:#fff"><i class="fas fa-file-invoice-dollar"></i> Emitir comprobante SUNAT</a>
    @endif
@endif
<a href="{{ route('tpv.ticket', $pedido) }}" target="_blank" class="btn btn-info"><i class="fas fa-print"></i> Imprimir ticket</a>
<a href="{{ route('pedidos.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
@endsection

@section('contenido')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between"><span><i class="fas fa-list"></i> Detalle del pedido</span>
                <span class="badge bg-{{ $pedido->colorEstado() }} fs-6">{{ \App\Models\Pedido::ESTADOS[$pedido->estado] }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table tabla-bonita mb-0">
                    <thead><tr><th>Producto</th><th class="text-center">Cantidad</th><th class="text-right">Precio</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                        @foreach($pedido->detalles as $d)
                        <tr>
                            <td>{{ $d->nombre_producto }}@if($d->observaciones)<br><small class="text-muted"><i class="fas fa-comment"></i> {{ $d->observaciones }}</small>@endif</td>
                            <td class="text-center">{{ $d->cantidad }}</td>
                            <td class="text-right">{{ $config->formatearPrecio($d->precio_unitario) }}</td>
                            <td class="text-right fw-bold">{{ $config->formatearPrecio($d->total) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr><td colspan="3" class="text-right">Subtotal</td><td class="text-right">{{ $config->formatearPrecio($pedido->subtotal) }}</td></tr>
                        @if($pedido->descuento>0)<tr><td colspan="3" class="text-right">Descuento</td><td class="text-right text-danger">-{{ $config->formatearPrecio($pedido->descuento) }}</td></tr>@endif
                        @if($pedido->coste_envio>0)<tr><td colspan="3" class="text-right">Envío</td><td class="text-right">{{ $config->formatearPrecio($pedido->coste_envio) }}</td></tr>@endif
                        <tr><td colspan="3" class="text-right">IVA</td><td class="text-right">{{ $config->formatearPrecio($pedido->total_iva) }}</td></tr>
                        <tr class="fw-bold"><td colspan="3" class="text-right h5">TOTAL</td><td class="text-right h5 text-success">{{ $config->formatearPrecio($pedido->total) }}</td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($pedido->pagos->count())
        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-money-bill"></i> Pagos</div>
            <div class="card-body p-0">
                <table class="table tabla-bonita mb-0">
                    <thead><tr><th>Fecha</th><th>Método</th><th class="text-right">Importe</th></tr></thead>
                    <tbody>
                    @foreach($pedido->pagos as $pg)
                    <tr><td>{{ $pg->fecha->format('d/m/Y H:i') }}</td><td>{{ ucfirst($pg->metodo) }}</td><td class="text-right fw-bold">{{ $config->formatearPrecio($pg->importe) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle"></i> Información</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Número</span><strong>{{ $pedido->numero }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Fecha</span><strong>{{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Tipo</span><strong>{{ \App\Models\Pedido::TIPOS[$pedido->tipo] }}</strong></li>
                @if($pedido->mesa)<li class="list-group-item d-flex justify-content-between"><span class="text-muted">Mesa</span><strong>{{ $pedido->mesa->numero }}</strong></li>@endif
                @if($pedido->cliente)<li class="list-group-item"><span class="text-muted">Cliente</span><br><strong>{{ $pedido->cliente->nombre_completo }}</strong>@if($pedido->cliente->telefono)<br><small><i class="fas fa-phone"></i> {{ $pedido->cliente->telefono }}</small>@endif</li>@endif
                @if($pedido->cliente_direccion)<li class="list-group-item"><span class="text-muted">Dirección entrega</span><br>{{ $pedido->cliente_direccion }}</li>@endif
                <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Cajero</span><strong>{{ $pedido->user->name ?? '—' }}</strong></li>
            </ul>
        </div>

        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-exchange-alt"></i> Cambiar estado</div>
            <div class="card-body">
                <form method="POST" action="{{ route('pedidos.estado',$pedido) }}">
                    @csrf @method('PATCH')
                    <select name="estado" class="form-control mb-2">
                        @foreach(\App\Models\Pedido::ESTADOS as $k=>$v)<option value="{{ $k }}" @selected($pedido->estado==$k)>{{ $v }}</option>@endforeach
                    </select>
                    <button class="btn btn-primary w-100"><i class="fas fa-save"></i> Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
