<!DOCTYPE html>
<html><head>
<meta charset="utf-8"><title>Ticket {{ $pedido->numero }}</title>
<style>
@page{margin:5mm}
body{font-family:'Courier New',monospace;font-size:12px;width:{{ $config->ancho_ticket_mm ?? 80 }}mm;margin:0;padding:0}
.center{text-align:center} .right{text-align:right}
.bold{font-weight:bold} hr{border:0;border-top:1px dashed #000;margin:6px 0}
table{width:100%;border-collapse:collapse} td{vertical-align:top;padding:1px 0}
.total{font-size:1.4em;font-weight:bold}
img.logo{max-width:60mm;max-height:30mm;display:block;margin:0 auto 4px}
</style>
</head><body onload="window.print()">
@if($config->imprimir_logo_ticket && $config->logo)<img src="{{ asset('storage/'.$config->logo) }}" class="logo">@endif
<div class="center bold">{{ $config->nombre_empresa }}</div>
@if($config->cif_nif)<div class="center">CIF/NIF: {{ $config->cif_nif }}</div>@endif
@if($config->direccion)<div class="center">{{ $config->direccion }}</div>@endif
@if($config->ciudad)<div class="center">{{ $config->codigo_postal }} {{ $config->ciudad }}</div>@endif
@if($config->telefono)<div class="center">Tel: {{ $config->telefono }}</div>@endif
@if($config->texto_ticket_cabecera)<hr><div class="center">{!! nl2br(e($config->texto_ticket_cabecera)) !!}</div>@endif
<hr>
<div><strong>Pedido:</strong> {{ $pedido->numero }}</div>
<div><strong>Fecha:</strong> {{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</div>
<div><strong>Tipo:</strong> {{ \App\Models\Pedido::TIPOS[$pedido->tipo] }}</div>
@if($pedido->mesa)<div><strong>Mesa:</strong> {{ $pedido->mesa->numero }}</div>@endif
@if($pedido->cliente)<div><strong>Cliente:</strong> {{ $pedido->cliente->nombre_completo }}</div>@endif
<hr>
<table>
@foreach($pedido->detalles as $d)
<tr><td colspan="3" class="bold">{{ $d->cantidad }}x {{ $d->nombre_producto }}</td></tr>
<tr><td></td><td class="right">{{ number_format($d->precio_unitario,2) }}</td><td class="right bold">{{ number_format($d->total,2) }}</td></tr>
@if($d->observaciones)<tr><td colspan="3" style="font-style:italic">  &raquo; {{ $d->observaciones }}</td></tr>@endif
@endforeach
</table>
<hr>
<table>
<tr><td>Subtotal</td><td class="right">{{ number_format($pedido->subtotal,2) }}</td></tr>
@if($pedido->descuento>0)<tr><td>Descuento</td><td class="right">-{{ number_format($pedido->descuento,2) }}</td></tr>@endif
@if($pedido->coste_envio>0)<tr><td>Envío</td><td class="right">{{ number_format($pedido->coste_envio,2) }}</td></tr>@endif
<tr><td class="bold">IVA</td><td class="right">{{ number_format($pedido->total_iva,2) }}</td></tr>
<tr><td class="total">TOTAL</td><td class="right total">{{ number_format($pedido->total,2) }} {{ $config->moneda_simbolo }}</td></tr>
</table>
@if($pedido->pagos->count())<hr>
<table>
@foreach($pedido->pagos as $pg)<tr><td>{{ ucfirst($pg->metodo) }}</td><td class="right">{{ number_format($pg->importe,2) }}</td></tr>@endforeach
</table>@endif
@if($config->texto_ticket_pie)<hr><div class="center">{!! nl2br(e($config->texto_ticket_pie)) !!}</div>@endif
<div class="center" style="margin-top:8px">¡Gracias por su visita!</div>
</body></html>
