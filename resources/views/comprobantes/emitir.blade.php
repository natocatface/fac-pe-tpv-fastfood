@extends('layouts.app')
@section('titulo', 'Emitir comprobante SUNAT')
@section('subtitulo', 'Pedido '.$pedido->numero)

@section('contenido')
<form method="POST" action="{{ route('comprobantes.emitir.store', $pedido) }}">
    @csrf
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><i class="fas fa-file-invoice"></i> Datos del comprobante</div>
                <div class="card-body">
                    @if($errors->any())<div class="alert alert-danger">{!! implode('<br>', $errors->all()) !!}</div>@endif

                    <div class="mb-3">
                        <label class="fw-bold mb-2 d-block">Tipo de comprobante</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="border rounded-3 p-3 d-flex gap-2 align-items-start cursor-pointer h-100">
                                    <input type="radio" name="tipo" value="01" class="mt-1" onclick="cambiarTipo('01')" id="tipo01">
                                    <div><strong>01 - Factura</strong><br><small class="text-muted">Para clientes con RUC (empresas)</small></div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="border rounded-3 p-3 d-flex gap-2 align-items-start cursor-pointer h-100" style="background:#f8fff8">
                                    <input type="radio" name="tipo" value="03" class="mt-1" onclick="cambiarTipo('03')" id="tipo03" checked>
                                    <div><strong>03 - Boleta de venta</strong><br><small class="text-muted">Para clientes con DNI (personas)</small></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tipo documento</label>
                            <select name="tipo_doc_receptor" class="form-control" id="tipoDoc" onchange="validarDoc()">
                                @foreach(\App\Models\ComprobanteElectronico::TIPOS_DOC_RECEPTOR as $k=>$v)
                                    <option value="{{ $k }}" @selected(old('tipo_doc_receptor', $pedido->cliente?->tipo_documento_pe ?? '1')==$k)>{{ $k }} - {{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">Número de documento *</label>
                            <input type="text" name="num_doc_receptor" id="numDoc" class="form-control" value="{{ old('num_doc_receptor', $pedido->cliente?->nif_cif) }}" required onblur="validarDoc()">
                            <div class="small text-muted" id="docHelp">DNI: 8 dígitos · RUC: 11 dígitos</div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Razón social / Nombre completo *</label>
                            <input type="text" name="razon_social_receptor" class="form-control" value="{{ old('razon_social_receptor', $pedido->cliente?->razon_social ?: $pedido->cliente?->nombre_completo ?: $pedido->cliente_nombre) }}" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Dirección</label>
                            <input type="text" name="direccion_receptor" class="form-control" value="{{ old('direccion_receptor', $pedido->cliente?->direccion ?: $pedido->cliente_direccion) }}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Email (envío automático)</label>
                            <input type="email" name="email_receptor" class="form-control" value="{{ old('email_receptor', $pedido->cliente?->email) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><i class="fas fa-receipt"></i> Resumen del pedido</div>
                <div class="card-body">
                    <p><strong>Pedido:</strong> {{ $pedido->numero }}</p>
                    <p><strong>Fecha:</strong> {{ $pedido->fecha_pedido->format('d/m/Y H:i') }}</p>
                    <p><strong>Tipo venta:</strong> {{ \App\Models\Pedido::TIPOS[$pedido->tipo] ?? $pedido->tipo }}</p>
                    <hr>
                    @foreach($pedido->detalles as $d)
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $d->cantidad }}× {{ $d->nombre_producto }}</span>
                            <span>S/ {{ number_format($d->total, 2) }}</span>
                        </div>
                    @endforeach
                    <hr>
                    <div class="d-flex justify-content-between fw-bold h5"><span>Total</span><span class="text-success">S/ {{ number_format($pedido->total, 2) }}</span></div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary w-100"><i class="fas fa-file-invoice-dollar"></i> Emitir y enviar a SUNAT</button>
                </div>
            </div>
            <div class="alert alert-info mt-3 small">
                <i class="fas fa-info-circle"></i> Tras emitir, se generará el XML, se firmará digitalmente y se enviará al servicio SUNAT.
            </div>
        </div>
    </div>
</form>

@endsection

@section('scripts')
<script>
function cambiarTipo(tipo){
    const select = document.getElementById('tipoDoc');
    // Factura sólo con RUC
    if (tipo === '01') select.value = '6';
    else if (tipo === '03' && select.value === '6') select.value = '1';
    validarDoc();
}
function validarDoc(){
    const td = document.getElementById('tipoDoc').value;
    const help = document.getElementById('docHelp');
    if (td === '1') help.textContent = 'DNI: 8 dígitos';
    else if (td === '6') help.textContent = 'RUC: 11 dígitos (empieza por 1 o 2)';
    else if (td === '7') help.textContent = 'Pasaporte: hasta 12 caracteres';
    else help.textContent = 'Documento de identidad';
}
validarDoc();
</script>
@endsection
