@extends('layouts.app')
@section('titulo', $comprobante->numero_completo)
@section('subtitulo', \App\Models\ComprobanteElectronico::TIPOS[$comprobante->tipo] . ' electrónica')

@section('botones')
@if(in_array($comprobante->estado, ['borrador','generado','firmado','error']))
<form action="{{ route('comprobantes.procesar', $comprobante) }}" method="POST" class="d-inline">
    @csrf
    <button class="btn btn-success" onclick="this.innerHTML='<i class=fa-spinner fa-spin></i> Procesando...';this.disabled=true;this.form.submit();return false;">
        <i class="fas fa-paper-plane"></i> Procesar y enviar a SUNAT
    </button>
</form>
@endif
@if(in_array($comprobante->estado, ['aceptado','observado']))
<a href="{{ route('comprobantes.pdf', $comprobante) }}" target="_blank" class="btn btn-danger">
    <i class="fas fa-file-pdf"></i> Ver / Imprimir PDF
</a>
@if(!in_array($comprobante->estado, ['anulado']))
<button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#modalAnular" data-target="#modalAnular">
    <i class="fas fa-ban"></i> Anular
</button>
@endif
@endif
<a href="{{ route('comprobantes.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
@endsection

@section('estilos')
<style>
.hero-comp{background:linear-gradient(135deg,#c8102e,#1a2332); color:#fff; padding:1.5rem; border-radius:14px; margin-bottom:1rem}
.estado-grande{font-size:1.1rem; padding:.4rem .9rem; border-radius:50px; font-weight:600; display:inline-flex; align-items:center; gap:.4rem}
.dato-fila{display:flex; padding:.55rem 0; border-bottom:1px dashed #e9ecef; align-items:start; gap:.6rem}
.dato-fila:last-child{border-bottom:0}
.dato-fila .lbl{flex:0 0 140px; color:#6c757d; font-size:.85rem}
.dato-fila .val{flex:1; font-weight:500; color:#1a2332; font-size:.9rem}
.archivo-card{background:#f8f9fa; border-radius:10px; padding:.85rem 1rem; margin-bottom:.5rem; display:flex; align-items:center; gap:.7rem}
.archivo-card .ic{width:38px; height:38px; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; flex-shrink:0}
.cobertura-table{width:100%; font-size:.85rem; margin-top:1rem}
.cobertura-table th{background:#f8f9fa; padding:.5rem .6rem; font-size:.75rem; text-transform:uppercase; color:#6c757d}
.cobertura-table td{padding:.55rem .6rem; border-bottom:1px solid #f1f3f5}
</style>
@endsection

@section('contenido')

<div class="hero-comp">
    <div class="d-flex align-items-center">
        <div class="flex-grow-1">
            <div class="small opacity-75">{{ \App\Models\ComprobanteElectronico::TIPOS[$comprobante->tipo] }} electrónica</div>
            <h2 class="mb-2" style="font-weight:700">{{ $comprobante->numero_completo }}</h2>
            <span class="estado-grande bg-{{ $comprobante->colorEstado() }} text-white">
                <i class="{{ $comprobante->iconoEstado() }}"></i>
                {{ \App\Models\ComprobanteElectronico::ESTADOS[$comprobante->estado] }}
            </span>
        </div>
        <div class="text-right">
            <div class="small opacity-75">TOTAL</div>
            <div style="font-size:2.2rem; font-weight:800">{{ $comprobante->moneda }} {{ number_format($comprobante->total, 2) }}</div>
            <div class="small opacity-75">IGV: {{ number_format($comprobante->igv, 2) }}</div>
        </div>
    </div>
</div>

@if($comprobante->codigo_sunat || $comprobante->mensaje_sunat)
<div class="alert alert-{{ $comprobante->estado === 'aceptado' ? 'success' : ($comprobante->estado === 'rechazado' ? 'danger' : 'warning') }} shadow-sm">
    <strong><i class="fas fa-info-circle"></i> Respuesta SUNAT:</strong>
    @if($comprobante->codigo_sunat)<span class="badge bg-dark">[{{ $comprobante->codigo_sunat }}]</span>@endif
    {{ $comprobante->mensaje_sunat }}
    @if(!empty($comprobante->observaciones))
        <ul class="mb-0 mt-2 small">
            @foreach($comprobante->observaciones as $obs)<li>{{ $obs }}</li>@endforeach
        </ul>
    @endif
</div>
@endif

<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header"><i class="fas fa-list"></i> Detalle del comprobante</div>
            <div class="card-body p-0">
                <table class="cobertura-table">
                    <thead><tr><th>#</th><th>Descripción</th><th class="text-center">Cant.</th><th class="text-right">P.Unit (s/IGV)</th><th class="text-right">IGV</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                        @foreach($comprobante->lineas as $l)
                            <tr>
                                <td>{{ $l->orden }}</td>
                                <td>
                                    <strong>{{ $l->descripcion }}</strong>
                                    @if($l->codigo_producto)<br><small class="text-muted">Cód: {{ $l->codigo_producto }}</small>@endif
                                </td>
                                <td class="text-center">{{ rtrim(rtrim(number_format($l->cantidad, 2), '0'), '.') }} {{ $l->unidad_medida }}</td>
                                <td class="text-right">{{ number_format($l->valor_unitario, 4) }}</td>
                                <td class="text-right">{{ number_format($l->igv, 2) }}</td>
                                <td class="text-right fw-bold">{{ number_format($l->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background:#f8f9fa">
                        <tr><td colspan="5" class="text-right">Op. Gravada</td><td class="text-right">{{ number_format($comprobante->total_gravado, 2) }}</td></tr>
                        <tr><td colspan="5" class="text-right">IGV ({{ rtrim(rtrim(number_format($config->igv_porcentaje ?? 18, 2),'0'),'.') }}%)</td><td class="text-right">{{ number_format($comprobante->igv, 2) }}</td></tr>
                        <tr><td colspan="5" class="text-right fw-bold h5">Importe Total</td><td class="text-right fw-bold h5">{{ $comprobante->moneda }} {{ number_format($comprobante->total, 2) }}</td></tr>
                    </tfoot>
                </table>
                @if($comprobante->total_letras)
                <div class="px-3 py-2 small fst-italic text-muted border-top">{{ $comprobante->total_letras }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-user-tag"></i> Receptor</div>
            <div class="card-body">
                <div class="dato-fila"><span class="lbl">Tipo doc.</span><span class="val">{{ \App\Models\ComprobanteElectronico::TIPOS_DOC_RECEPTOR[$comprobante->tipo_doc_receptor] ?? '-' }}</span></div>
                <div class="dato-fila"><span class="lbl">Número</span><span class="val">{{ $comprobante->num_doc_receptor }}</span></div>
                <div class="dato-fila"><span class="lbl">Razón social</span><span class="val">{{ $comprobante->razon_social_receptor }}</span></div>
                @if($comprobante->direccion_receptor)<div class="dato-fila"><span class="lbl">Dirección</span><span class="val">{{ $comprobante->direccion_receptor }}</span></div>@endif
                @if($comprobante->email_receptor)<div class="dato-fila"><span class="lbl">Email</span><span class="val">{{ $comprobante->email_receptor }}</span></div>@endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-info-circle"></i> Información SUNAT</div>
            <div class="card-body">
                <div class="dato-fila"><span class="lbl">Fecha emisión</span><span class="val">{{ $comprobante->fecha_emision->format('d/m/Y') }}</span></div>
                @if($comprobante->hora_emision)<div class="dato-fila"><span class="lbl">Hora</span><span class="val">{{ $comprobante->hora_emision }}</span></div>@endif
                <div class="dato-fila"><span class="lbl">Moneda</span><span class="val">{{ $comprobante->moneda }}</span></div>
                <div class="dato-fila"><span class="lbl">Intentos envío</span><span class="val">{{ $comprobante->intentos_envio }}</span></div>
                @if($comprobante->enviado_at)<div class="dato-fila"><span class="lbl">Enviado</span><span class="val">{{ $comprobante->enviado_at->format('d/m/Y H:i') }}</span></div>@endif
                @if($comprobante->aceptado_at)<div class="dato-fila"><span class="lbl">Aceptado</span><span class="val">{{ $comprobante->aceptado_at->format('d/m/Y H:i') }}</span></div>@endif
                @if($comprobante->hash_xml)<div class="dato-fila"><span class="lbl">Digest</span><span class="val small" style="word-break:break-all">{{ Str::limit($comprobante->hash_xml, 30) }}</span></div>@endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i class="fas fa-download"></i> Archivos</div>
            <div class="card-body">
                @if($comprobante->xml_path)
                <div class="archivo-card">
                    <div class="ic" style="background:#17a2b8"><i class="fas fa-file-code"></i></div>
                    <div class="flex-grow-1">
                        <strong>XML original</strong>
                        <div class="small text-muted">Sin firma</div>
                    </div>
                    <a href="{{ route('comprobantes.descargar', [$comprobante, 'xml']) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i></a>
                </div>
                @endif
                @if($comprobante->xml_firmado_path)
                <div class="archivo-card">
                    <div class="ic" style="background:#28a745"><i class="fas fa-signature"></i></div>
                    <div class="flex-grow-1">
                        <strong>XML firmado</strong>
                        <div class="small text-muted">Con firma digital XMLDSig</div>
                    </div>
                    <a href="{{ route('comprobantes.descargar', [$comprobante, 'firmado']) }}" class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i></a>
                </div>
                @endif
                @if($comprobante->cdr_path)
                <div class="archivo-card">
                    <div class="ic" style="background:#dc3545"><i class="fas fa-stamp"></i></div>
                    <div class="flex-grow-1">
                        <strong>CDR SUNAT</strong>
                        <div class="small text-muted">Constancia de Recepción (ZIP)</div>
                    </div>
                    <a href="{{ route('comprobantes.descargar', [$comprobante, 'cdr']) }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-download"></i></a>
                </div>
                @endif
                @if($comprobante->pdf_path)
                <div class="archivo-card">
                    <div class="ic" style="background:#c8102e"><i class="fas fa-file-pdf"></i></div>
                    <div class="flex-grow-1">
                        <strong>PDF (Representación impresa)</strong>
                        <div class="small text-muted">Con código QR SUNAT</div>
                    </div>
                    <a href="{{ route('comprobantes.pdf', $comprobante) }}" target="_blank" class="btn btn-sm btn-outline-danger"><i class="fas fa-eye"></i></a>
                </div>
                @endif
                @if(!$comprobante->xml_path && !$comprobante->xml_firmado_path && !$comprobante->cdr_path && !$comprobante->pdf_path)
                <p class="text-muted small mb-0 text-center py-2">Aún no se han generado archivos.<br>Pulsa "Procesar y enviar a SUNAT".</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ============== MODAL ANULAR ============== --}}
<div class="modal fade" id="modalAnular" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-ban"></i> Anular comprobante</h5>
                <button type="button" class="btn-close close text-dark" data-bs-dismiss="modal" data-dismiss="modal">×</button>
            </div>
            <form method="POST" action="{{ route('comprobantes.anular', $comprobante) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle"></i>
                        Se enviará una <strong>Comunicación de Baja</strong> a SUNAT. El comprobante quedará anulado fiscalmente.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo de anulación *</label>
                        <textarea name="motivo" class="form-control" rows="3" required maxlength="250" placeholder="Ej: Error en el documento del cliente, devolución del pedido..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-ban"></i> Confirmar anulación</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
