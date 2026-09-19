@extends('layouts.app')
@section('titulo', 'Comprobantes Electrónicos')
@section('subtitulo', 'Facturación electrónica SUNAT · Perú')

@section('estilos')
<style>
.hero-sunat{
    background: linear-gradient(135deg,#c8102e 0%, #ee2737 50%, #1a2332 100%);
    border-radius:18px; padding:1.5rem 2rem; color:#fff; position:relative; overflow:hidden;
    box-shadow:0 12px 30px rgba(200,16,46,.20); margin-bottom:1.25rem;
}
.hero-sunat::after{content:""; position:absolute; right:-80px; top:-80px; width:280px; height:280px; border-radius:50%; background:radial-gradient(circle, rgba(255,255,255,.15) 0%, transparent 70%)}
.hero-sunat .logo-sunat{width:80px; height:80px; border-radius:18px; background:rgba(255,255,255,.18); backdrop-filter:blur(6px); display:flex; align-items:center; justify-content:center; font-size:2rem; flex-shrink:0}
.kpi-sunat{background:#fff; border-radius:14px; padding:1.1rem; box-shadow:0 2px 12px rgba(0,0,0,.05); transition:transform .15s, box-shadow .15s; height:100%; border-left:4px solid transparent}
.kpi-sunat:hover{transform:translateY(-3px); box-shadow:0 12px 26px rgba(0,0,0,.1)}
.kpi-sunat .num{font-size:1.8rem; font-weight:700; line-height:1.1; margin:0}
.kpi-sunat .lbl{font-size:.78rem; color:#6c757d; text-transform:uppercase; letter-spacing:.5px}
.kpi-sunat.success{border-left-color:#28a745} .kpi-sunat.success .num{color:#28a745}
.kpi-sunat.warn{border-left-color:#ffc107} .kpi-sunat.warn .num{color:#d39e00}
.kpi-sunat.danger{border-left-color:#dc3545} .kpi-sunat.danger .num{color:#dc3545}
.kpi-sunat.info{border-left-color:#17a2b8} .kpi-sunat.info .num{color:#17a2b8}

.comprobante-row{transition:background .12s}
.comprobante-row:hover{background:#f6f8fb !important}
.tipo-badge{display:inline-block; padding:.25rem .55rem; border-radius:6px; font-size:.7rem; font-weight:700; letter-spacing:.5px}
.tipo-01{background:#1a2332; color:#fff}
.tipo-03{background:#17a2b8; color:#fff}
.tipo-07{background:#ffc107; color:#212529}
.tipo-08{background:#fd7e14; color:#fff}

.estado-pill{padding:.2rem .6rem; border-radius:50px; font-size:.72rem; font-weight:600; display:inline-flex; align-items:center; gap:.3rem}
</style>
@endsection

@section('contenido')
<div class="hero-sunat">
    <div class="d-flex align-items-center" style="position:relative; z-index:2">
        <div class="logo-sunat me-3 mr-3"><i class="fas fa-file-invoice-dollar"></i></div>
        <div class="flex-grow-1">
            <h3 class="mb-1" style="font-weight:700">Facturación Electrónica SUNAT</h3>
            <p class="mb-0 opacity-90">Emite facturas y boletas electrónicas directamente a SUNAT (Perú) sin intermediarios</p>
        </div>
        <div class="text-right">
            @php $cfg = $config; @endphp
            @if($cfg && $cfg->facturacion_electronica_pe)
                <span class="badge bg-success">● Módulo activo</span>
                <div class="small mt-1">Modo: <strong>{{ strtoupper($cfg->sunat_modo) }}</strong></div>
                @if($cfg->ruc)<div class="small">RUC {{ $cfg->ruc }}</div>@endif
            @else
                <span class="badge bg-warning text-dark">● Módulo desactivado</span>
                <div class="small mt-1"><a href="{{ route('configuracion.edit') }}#tab-sunat" class="text-white">Activar en Configuración →</a></div>
            @endif
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3 mb-2"><div class="kpi-sunat info"><div class="lbl">Total emitidos</div><h2 class="num">{{ number_format($stats['total']) }}</h2></div></div>
    <div class="col-md-3 mb-2"><div class="kpi-sunat success"><div class="lbl">Aceptados SUNAT</div><h2 class="num">{{ number_format($stats['aceptados']) }}</h2></div></div>
    <div class="col-md-3 mb-2"><div class="kpi-sunat warn"><div class="lbl">En proceso</div><h2 class="num">{{ number_format($stats['pendientes']) }}</h2></div></div>
    <div class="col-md-3 mb-2"><div class="kpi-sunat danger"><div class="lbl">Rechazados / Error</div><h2 class="num">{{ number_format($stats['rechazados']) }}</h2></div></div>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Nº, RUC/DNI, razón social..."></div>
            <div class="col-md-2"><select name="tipo" class="form-control form-control-sm"><option value="">Todos tipos</option>@foreach(\App\Models\ComprobanteElectronico::TIPOS as $k=>$v)<option value="{{ $k }}" @selected(request('tipo')==$k)>{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-2"><select name="estado" class="form-control form-control-sm"><option value="">Todos estados</option>@foreach(\App\Models\ComprobanteElectronico::ESTADOS as $k=>$v)<option value="{{ $k }}" @selected(request('estado')==$k)>{{ $v }}</option>@endforeach</select></div>
            <div class="col-md-2"><input type="date" name="desde" class="form-control form-control-sm" value="{{ request('desde') }}"></div>
            <div class="col-md-2"><input type="date" name="hasta" class="form-control form-control-sm" value="{{ request('hasta') }}"></div>
            <div class="col-md-1"><button class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i></button></div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table tabla-bonita mb-0">
            <thead><tr>
                <th>Comprobante</th><th>Fecha</th><th>Receptor</th>
                <th class="text-right">Total</th><th>Estado</th><th class="text-right">Acciones</th>
            </tr></thead>
            <tbody>
                @forelse($comprobantes as $c)
                <tr class="comprobante-row">
                    <td>
                        <span class="tipo-badge tipo-{{ $c->tipo }}">{{ \App\Models\ComprobanteElectronico::TIPOS[$c->tipo] }}</span>
                        <div class="fw-bold mt-1">{{ $c->numero_completo }}</div>
                    </td>
                    <td>
                        <strong>{{ $c->fecha_emision->format('d/m/Y') }}</strong>
                        <div class="small text-muted">{{ $c->hora_emision ? \Carbon\Carbon::parse($c->hora_emision)->format('H:i') : '' }}</div>
                    </td>
                    <td>
                        <strong>{{ $c->razon_social_receptor }}</strong>
                        <div class="small text-muted">
                            {{ \App\Models\ComprobanteElectronico::TIPOS_DOC_RECEPTOR[$c->tipo_doc_receptor] ?? 'Doc' }}: {{ $c->num_doc_receptor }}
                        </div>
                    </td>
                    <td class="text-right">
                        <strong>S/ {{ number_format($c->total, 2) }}</strong>
                        <div class="small text-muted">IGV: S/ {{ number_format($c->igv, 2) }}</div>
                    </td>
                    <td>
                        <span class="estado-pill bg-{{ $c->colorEstado() }} text-white">
                            <i class="{{ $c->iconoEstado() }}"></i> {{ \App\Models\ComprobanteElectronico::ESTADOS[$c->estado] }}
                        </span>
                        @if($c->codigo_sunat)
                            <div class="small text-muted mt-1">SUNAT [{{ $c->codigo_sunat }}]</div>
                        @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ route('comprobantes.show', $c) }}" class="btn btn-sm btn-outline-primary" title="Ver"><i class="fas fa-eye"></i></a>
                        @if($c->xml_firmado_path)
                            <a href="{{ route('comprobantes.descargar', [$c, 'firmado']) }}" class="btn btn-sm btn-outline-info" title="XML firmado"><i class="fas fa-file-code"></i></a>
                        @endif
                        @if($c->cdr_path)
                            <a href="{{ route('comprobantes.descargar', [$c, 'cdr']) }}" class="btn btn-sm btn-outline-success" title="CDR SUNAT"><i class="fas fa-stamp"></i></a>
                        @endif
                        @if(in_array($c->estado, ['aceptado','observado']))
                            <a href="{{ route('comprobantes.pdf', $c) }}?refresh=1" target="_blank" class="btn btn-sm btn-outline-danger" title="PDF"><i class="fas fa-file-pdf"></i></a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-5 text-muted">
                    <i class="far fa-file fa-3x mb-2 opacity-50"></i><br>
                    <strong>Aún no hay comprobantes electrónicos emitidos.</strong>
                    <div class="small mt-1">Para emitir, ve a un pedido y pulsa "Emitir comprobante SUNAT".</div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $comprobantes->links() }}</div>
</div>
@endsection
