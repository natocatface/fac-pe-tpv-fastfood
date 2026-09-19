<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>{{ $comprobante->numero_completo }} - {{ $config->ruc }}</title>
<style>
    @page { margin: 12mm; }
    * { font-family: DejaVu Sans, Arial, sans-serif; }
    body { font-size: 9pt; color: #1a1a1a; margin:0; padding:0; }
    table { width: 100%; border-collapse: collapse; }
    .pdf-header { width:100%; margin-bottom: 6mm; }
    .empresa-block { width:62%; vertical-align: top; }
    .doc-block { width:38%; vertical-align: top; padding-left:4mm; }

    .empresa-nombre { font-size: 16pt; font-weight: bold; color:#c8102e; margin:0 0 2mm 0; }
    .empresa-info   { font-size: 9pt; line-height: 1.4; }

    .doc-caja {
        border:2px solid #c8102e; border-radius:6px; padding:4mm; text-align:center;
        background:#fff;
    }
    .doc-tipo { font-size: 10pt; font-weight: bold; color:#c8102e; text-transform:uppercase; letter-spacing:.5px; }
    .doc-numero { font-size: 14pt; font-weight: bold; margin:1mm 0; }
    .doc-ruc { font-size: 9pt; font-weight: bold; }
    .doc-electronica { font-size:7pt; color:#666; margin-top:1mm; }

    .receptor-box {
        background:#f8f9fa; padding:3mm 4mm; margin: 4mm 0 3mm;
        border-left: 3px solid #1a2332; border-radius:3px;
    }
    .receptor-box .lbl { font-size: 7.5pt; color:#5f5e5a; text-transform: uppercase; letter-spacing: .5px; }
    .receptor-box .val { font-size: 9.5pt; font-weight: bold; }
    .receptor-row td { padding: 1mm 4mm 1mm 0; vertical-align: top; }

    .lineas { width:100%; border-collapse:collapse; margin-top:3mm; font-size:8.5pt; }
    .lineas thead th {
        background:#1a2332; color:#fff; padding:2.5mm 2mm; font-weight: bold;
        font-size: 7.5pt; text-transform:uppercase; letter-spacing: .5px;
    }
    .lineas tbody td { padding:2mm; border-bottom: 0.5pt solid #e9ecef; vertical-align: top; }
    .lineas .num { text-align: right; }
    .lineas .center { text-align: center; }

    .totales-table { width:55%; margin-left:45%; margin-top:3mm; font-size: 9pt; }
    .totales-table td { padding: 1.2mm 3mm; }
    .totales-table .lbl { text-align: right; color:#5f5e5a; }
    .totales-table .val { text-align: right; font-weight: bold; }
    .totales-table .total-row { background:#1a2332; color:#fff; font-size: 11pt; }
    .totales-table .total-row td { padding: 2.5mm 3mm; }

    .letras-box {
        background:#f8f9fa; padding:3mm 4mm; margin-top: 3mm; font-style: italic;
        font-size: 8.5pt; border-radius: 3px;
    }

    .footer-section { margin-top: 5mm; }
    .qr-box { width:35%; vertical-align: top; padding-right: 4mm; }
    .qr-box img { width: 32mm; height: 32mm; }
    .qr-hash {
        font-size: 6.5pt; word-break: break-all; color:#5f5e5a;
        margin-top: 1mm; line-height: 1.3;
    }
    .info-final { width:65%; vertical-align: top; font-size: 7.5pt; color:#5f5e5a; line-height: 1.5; }
    .estado-sunat {
        display:inline-block; padding:1.5mm 3mm; border-radius:3px;
        font-size:7.5pt; font-weight: bold; text-transform: uppercase;
    }
    .aceptado { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
    .observado { background:#fff3cd; color:#856404; border:1px solid #ffeaa7; }
    .rechazado { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
    .pendiente { background:#e2e3e5; color:#383d41; border:1px solid #d6d8db; }

    .leyenda-impresion {
        text-align:center; font-size:7pt; color:#5f5e5a;
        margin-top:4mm; font-style: italic;
    }
</style>
</head>
<body>

{{-- ========== CABECERA ========== --}}
<table class="pdf-header">
    <tr>
        <td class="empresa-block">
            @if($config->logo)
                <img src="{{ public_path('storage/'.$config->logo) }}" style="max-height:18mm; max-width:50mm; margin-bottom:2mm;" alt="logo">
            @endif
            <div class="empresa-nombre">{{ $config->razon_social ?: $config->nombre_empresa }}</div>
            <div class="empresa-info">
                @if($config->nombre_empresa && $config->razon_social && $config->nombre_empresa !== $config->razon_social)
                    <strong>{{ $config->nombre_empresa }}</strong><br>
                @endif
                {{ $config->direccion }}@if($config->urbanizacion), {{ $config->urbanizacion }}@endif<br>
                @if($config->distrito){{ $config->distrito }} - @endif{{ $config->ciudad }}@if($config->departamento), {{ $config->departamento }}@endif - {{ $config->pais }}<br>
                @if($config->telefono)<strong>Tel:</strong> {{ $config->telefono }} @endif
                @if($config->email)| <strong>Email:</strong> {{ $config->email }}@endif
                @if($config->web)<br><strong>Web:</strong> {{ $config->web }}@endif
            </div>
        </td>
        <td class="doc-block">
            <div class="doc-caja">
                <div class="doc-ruc">R.U.C. N° {{ $config->ruc }}</div>
                <div class="doc-tipo">
                    @if($comprobante->esFactura()) Factura Electrónica
                    @elseif($comprobante->esBoleta()) Boleta de Venta Electrónica
                    @elseif($comprobante->esNotaCredito()) Nota de Crédito Electrónica
                    @elseif($comprobante->esNotaDebito()) Nota de Débito Electrónica
                    @endif
                </div>
                <div class="doc-numero">{{ $comprobante->numero_completo }}</div>
                <div class="doc-electronica">
                    @if($comprobante->estado === 'aceptado')
                        <span class="estado-sunat aceptado">✓ Aceptado por SUNAT</span>
                    @elseif($comprobante->estado === 'observado')
                        <span class="estado-sunat observado">⚠ Aceptado con observaciones</span>
                    @elseif($comprobante->estado === 'rechazado')
                        <span class="estado-sunat rechazado">✗ Rechazado por SUNAT</span>
                    @else
                        <span class="estado-sunat pendiente">● {{ \App\Models\ComprobanteElectronico::ESTADOS[$comprobante->estado] }}</span>
                    @endif
                </div>
            </div>
        </td>
    </tr>
</table>

{{-- ========== RECEPTOR ========== --}}
<div class="receptor-box">
    <table class="receptor-row">
        <tr>
            <td><span class="lbl">Señor(es):</span><br><span class="val">{{ $comprobante->razon_social_receptor }}</span></td>
            <td><span class="lbl">Fecha de emisión:</span><br><span class="val">{{ $comprobante->fecha_emision->format('d/m/Y') }}</span></td>
        </tr>
        <tr>
            <td>
                <span class="lbl">{{ \App\Models\ComprobanteElectronico::TIPOS_DOC_RECEPTOR[$comprobante->tipo_doc_receptor] ?? 'Documento' }}:</span>
                <span class="val">{{ $comprobante->num_doc_receptor }}</span>
            </td>
            <td>
                <span class="lbl">Moneda:</span>
                <span class="val">{{ $comprobante->moneda === 'PEN' ? 'SOLES' : $comprobante->moneda }}</span>
            </td>
        </tr>
        @if($comprobante->direccion_receptor)
        <tr>
            <td colspan="2"><span class="lbl">Dirección:</span> <span class="val">{{ $comprobante->direccion_receptor }}</span></td>
        </tr>
        @endif
    </table>
</div>

{{-- ========== LÍNEAS ========== --}}
<table class="lineas">
    <thead>
        <tr>
            <th width="8%" class="center">Cant.</th>
            <th width="10%">Unidad</th>
            <th width="42%">Descripción</th>
            <th width="13%" class="num">V. Unitario</th>
            <th width="13%" class="num">P. Unitario</th>
            <th width="14%" class="num">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($comprobante->lineas as $linea)
        <tr>
            <td class="center">{{ rtrim(rtrim(number_format($linea->cantidad, 2), '0'), '.') }}</td>
            <td>{{ $linea->unidad_medida }}</td>
            <td>
                <strong>{{ $linea->descripcion }}</strong>
                @if($linea->codigo_producto)<br><span style="font-size:7pt;color:#666">Cód: {{ $linea->codigo_producto }}</span>@endif
            </td>
            <td class="num">{{ number_format($linea->valor_unitario, 4) }}</td>
            <td class="num">{{ number_format($linea->precio_unitario, 4) }}</td>
            <td class="num">{{ number_format($linea->total, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ========== TOTALES ========== --}}
<table class="totales-table">
    <tr>
        <td class="lbl">Op. Gravada:</td>
        <td class="val">{{ $comprobante->moneda === 'PEN' ? 'S/' : $comprobante->moneda }} {{ number_format($comprobante->total_gravado, 2) }}</td>
    </tr>
    @if($comprobante->total_exonerado > 0)
    <tr>
        <td class="lbl">Op. Exonerada:</td>
        <td class="val">{{ $comprobante->moneda === 'PEN' ? 'S/' : $comprobante->moneda }} {{ number_format($comprobante->total_exonerado, 2) }}</td>
    </tr>
    @endif
    @if($comprobante->total_inafecto > 0)
    <tr>
        <td class="lbl">Op. Inafecta:</td>
        <td class="val">{{ $comprobante->moneda === 'PEN' ? 'S/' : $comprobante->moneda }} {{ number_format($comprobante->total_inafecto, 2) }}</td>
    </tr>
    @endif
    @if($comprobante->total_descuento > 0)
    <tr>
        <td class="lbl">Descuento:</td>
        <td class="val">- {{ $comprobante->moneda === 'PEN' ? 'S/' : $comprobante->moneda }} {{ number_format($comprobante->total_descuento, 2) }}</td>
    </tr>
    @endif
    <tr>
        <td class="lbl">IGV ({{ rtrim(rtrim(number_format($config->igv_porcentaje ?? 18, 2),'0'),'.') }}%):</td>
        <td class="val">{{ $comprobante->moneda === 'PEN' ? 'S/' : $comprobante->moneda }} {{ number_format($comprobante->igv, 2) }}</td>
    </tr>
    <tr class="total-row">
        <td>IMPORTE TOTAL:</td>
        <td style="text-align:right">{{ $comprobante->moneda === 'PEN' ? 'S/' : $comprobante->moneda }} {{ number_format($comprobante->total, 2) }}</td>
    </tr>
</table>

{{-- ========== TOTAL EN LETRAS ========== --}}
@if($comprobante->total_letras)
<div class="letras-box">
    <strong>Son:</strong> {{ $comprobante->total_letras }}
</div>
@endif

{{-- ========== QR + INFO FINAL ========== --}}
<table class="footer-section">
    <tr>
        <td class="qr-box">
            @if($qrBase64)
                <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR SUNAT">
            @endif
            @if($comprobante->hash_xml)
                <div class="qr-hash">
                    <strong>Hash:</strong> {{ $comprobante->hash_xml }}
                </div>
            @endif
        </td>
        <td class="info-final">
            <strong>Información del comprobante electrónico:</strong><br>
            Representación impresa de la
            @if($comprobante->esFactura()) Factura
            @elseif($comprobante->esBoleta()) Boleta de Venta
            @elseif($comprobante->esNotaCredito()) Nota de Crédito
            @elseif($comprobante->esNotaDebito()) Nota de Débito
            @endif
            Electrónica.<br>
            Autorizado mediante Resolución de Intendencia N° 034-005-0000XXXX/SUNAT.<br>
            Consulte su validez en: <strong>www.sunat.gob.pe</strong><br>
            @if($comprobante->codigo_sunat)
                <br><strong>Código SUNAT:</strong> [{{ $comprobante->codigo_sunat }}] {{ $comprobante->mensaje_sunat }}
            @endif
        </td>
    </tr>
</table>

<div class="leyenda-impresion">
    Esta es la representación impresa del documento electrónico generado en el Sistema de Emisión Electrónica.
</div>

</body>
</html>
