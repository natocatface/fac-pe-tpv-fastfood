<?php

namespace App\Services\Sunat;

use App\Models\ComprobanteElectronico;
use App\Models\ComprobanteLinea;
use App\Models\Configuracion;
use App\Models\Pedido;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Orquestador: emite, firma y envía un comprobante a SUNAT.
 *
 *   $service->emitirDesdePedido($pedido, 'factura', '20123456789', 'Cliente SAC');
 *   $service->procesar($comprobante);  // genera XML + firma + envía
 */
class FacturacionElectronicaService
{
    public function __construct(
        private UblXmlBuilder           $builder,
        private FirmaDigitalService     $firmador,
        private ComprobantePdfService   $pdfService,
        private ResumenBajaXmlBuilder   $bajaBuilder,
    ) {}

    /**
     * Anula un comprobante aceptado enviando Comunicación de Baja a SUNAT.
     * Devuelve el ticket entregado por SUNAT (asíncrono).
     */
    public function anularComprobante(\App\Models\ComprobanteElectronico $c): array
    {
        $cfg = Configuracion::actual();

        // Calcular correlativo del día
        $correlativoHoy = \App\Models\ComprobanteElectronico::where('estado', 'anulado')
            ->whereDate('updated_at', now()->toDateString())
            ->count() + 1;

        $data = $this->bajaBuilder->construirComunicacionBaja($c, $correlativoHoy);
        $idRA = $data['identificador'];

        // Firmar
        $res = $this->firmador->firmar($data['xml']);
        $xmlFirmado = $res['xml'];

        // Guardar XML firmado
        $disk = Storage::disk('local');
        $dir = 'comprobantes/' . $c->fecha_emision->format('Y/m') . '/bajas';
        if (!$disk->exists($dir)) $disk->makeDirectory($dir);
        $nombre = "{$cfg->ruc}-{$idRA}.xml";
        $rutaXml = $dir . '/' . $nombre;
        $disk->put($rutaXml, $xmlFirmado);

        // Enviar a SUNAT (método sendSummary que devuelve ticket asíncrono)
        $cliente = new SunatSoapClient($cfg);
        $r = $cliente->enviarResumenBaja("{$cfg->ruc}-{$idRA}", $xmlFirmado);

        if (!empty($r['ok']) && !empty($r['ticket'])) {
            $c->update([
                'estado'           => 'anulado',
                'codigo_sunat'     => $r['ticket'],
                'mensaje_sunat'    => 'Comunicación de Baja enviada. Ticket: ' . $r['ticket'],
            ]);
            return ['ticket' => $r['ticket'], 'estado' => 'anulado'];
        }

        throw new \RuntimeException($r['mensaje'] ?? 'No se pudo enviar la Comunicación de Baja.');
    }

    /**
     * Crea un comprobante electrónico a partir de un pedido del TPV.
     */
    public function emitirDesdePedido(
        Pedido $pedido,
        string $tipo,             // 'factura' o 'boleta'
        string $tipoDocReceptor,  // '1' DNI, '6' RUC, '7' Pasaporte
        string $numDocReceptor,
        string $razonSocial,
        ?string $direccion = null,
        ?string $email     = null,
    ): ComprobanteElectronico {
        $cfg = Configuracion::actual();
        if (!$cfg->facturacion_electronica_pe) {
            throw new \RuntimeException('La facturación electrónica Perú no está activada.');
        }

        $tipoCodigo = ($tipo === 'factura' || $tipo === '01') ? '01' : '03';

        // Calcular numeración
        if ($tipoCodigo === '01') {
            $serie = $cfg->serie_factura_pe;
            $numero = $cfg->proximo_factura_pe;
            $cfg->increment('proximo_factura_pe');
        } else {
            $serie = $cfg->serie_boleta_pe;
            $numero = $cfg->proximo_boleta_pe;
            $cfg->increment('proximo_boleta_pe');
        }
        $numeroCompleto = $serie . '-' . str_pad((string) $numero, 8, '0', STR_PAD_LEFT);

        // Recalcular importes
        $pedido->load('detalles');
        $igvPct = (float) $cfg->igv_porcentaje;
        $factor = 1 + $igvPct / 100;

        $totalConIgv  = 0;
        $totalIgv     = 0;
        $totalGravado = 0;

        $comprobante = ComprobanteElectronico::create([
            'pedido_id'             => $pedido->id,
            'cliente_id'            => $pedido->cliente_id,
            'user_id'               => auth()->id() ?? $pedido->user_id,
            'tipo'                  => $tipoCodigo,
            'serie'                 => $serie,
            'numero'                => $numero,
            'numero_completo'       => $numeroCompleto,
            'fecha_emision'         => now()->toDateString(),
            'hora_emision'          => now()->format('H:i:s'),
            'tipo_doc_receptor'     => $tipoDocReceptor,
            'num_doc_receptor'      => $numDocReceptor,
            'razon_social_receptor' => $razonSocial,
            'direccion_receptor'    => $direccion,
            'email_receptor'        => $email,
            'moneda'                => 'PEN',
            'estado'                => 'borrador',
        ]);

        foreach ($pedido->detalles as $i => $d) {
            $totalLineaConIgv = (float) $d->total;
            $valorTotal = round($totalLineaConIgv / $factor, 2);
            $igvLinea   = round($totalLineaConIgv - $valorTotal, 2);
            $valorUnit  = round($valorTotal / max($d->cantidad, 1), 4);
            $precioUnit = round($totalLineaConIgv / max($d->cantidad, 1), 4);

            ComprobanteLinea::create([
                'comprobante_id'      => $comprobante->id,
                'producto_id'         => $d->producto_id,
                'orden'               => $i + 1,
                'codigo_producto'     => optional($d->producto)->codigo,
                'descripcion'         => $d->nombre_producto,
                'unidad_medida'       => 'NIU',
                'cantidad'            => $d->cantidad,
                'valor_unitario'      => $valorUnit,
                'precio_unitario'     => $precioUnit,
                'tipo_afectacion_igv' => '10', // Gravado - Operación onerosa
                'igv'                 => $igvLinea,
                'valor_total'         => $valorTotal,
                'total'               => $totalLineaConIgv,
            ]);

            $totalConIgv  += $totalLineaConIgv;
            $totalIgv     += $igvLinea;
            $totalGravado += $valorTotal;
        }

        $comprobante->update([
            'total_gravado' => round($totalGravado, 2),
            'igv'           => round($totalIgv, 2),
            'total'         => round($totalConIgv, 2),
            'total_letras'  => UblXmlBuilder::numeroALetras(round($totalConIgv, 2), 'SOLES'),
        ]);

        return $comprobante->fresh('lineas');
    }

    /**
     * Genera XML → firma → envía a SUNAT y actualiza el estado.
     */
    public function procesar(ComprobanteElectronico $c): ComprobanteElectronico
    {
        $cfg = Configuracion::actual();

        // 1. Generar XML
        $xml = $this->builder->construir($c);
        $rutaXml = $this->guardarArchivo($c, $cfg, '.xml', $xml);
        $c->update(['estado' => 'generado', 'xml_path' => $rutaXml]);

        // 2. Firmar XML
        try {
            $res = $this->firmador->firmar($xml);
            $rutaFirmado = $this->guardarArchivo($c, $cfg, '_firmado.xml', $res['xml']);
            $c->update([
                'estado'           => 'firmado',
                'xml_firmado_path' => $rutaFirmado,
                'hash_xml'         => $res['digest'],
            ]);
            $xmlFirmado = $res['xml'];
        } catch (\Throwable $e) {
            $c->update(['estado' => 'error', 'mensaje_sunat' => 'Firma: ' . $e->getMessage()]);
            return $c->fresh();
        }

        // 3. Enviar a SUNAT
        $nombreSinExt = "{$cfg->ruc}-{$c->tipo}-{$c->serie}-" . str_pad((string) $c->numero, 8, '0', STR_PAD_LEFT);
        $cliente = new SunatSoapClient($cfg);
        $r = $cliente->enviarComprobante($nombreSinExt, $xmlFirmado);

        $c->update([
            'enviado_at'      => now(),
            'intentos_envio'  => $c->intentos_envio + 1,
            'codigo_sunat'    => $r['codigo']  ?? null,
            'mensaje_sunat'   => $r['mensaje'] ?? null,
            'observaciones'   => $r['observaciones'] ?? null,
        ]);

        if (!empty($r['ok'])) {
            // Guardar CDR
            $cdrPath = null;
            if (!empty($r['cdr_zip_b64'])) {
                $cdrPath = $this->guardarArchivo($c, $cfg, '_cdr.zip', base64_decode($r['cdr_zip_b64']));
            }
            $codigo = $r['codigo'] ?? '0';
            $estado = $codigo === '0' || $codigo === '' ? 'aceptado' : ($codigo[0] === '2' ? 'rechazado' : 'observado');
            $c->update([
                'estado'      => $estado,
                'cdr_path'    => $cdrPath,
                'aceptado_at' => $estado === 'aceptado' ? now() : null,
            ]);

            // Generar PDF representación impresa (siempre que esté aceptado u observado)
            if (in_array($estado, ['aceptado', 'observado'])) {
                try {
                    $this->pdfService->generarYGuardar($c);
                } catch (\Throwable $e) {
                    // No bloquear el flujo si falla el PDF
                    \Log::warning('Error generando PDF: ' . $e->getMessage());
                }
            }
        } else {
            $c->update(['estado' => 'error']);
        }

        return $c->fresh();
    }

    /** Guarda un archivo asociado al comprobante y devuelve el path relativo. */
    private function guardarArchivo(ComprobanteElectronico $c, Configuracion $cfg, string $suffix, string $contenido): string
    {
        $disk = Storage::disk('local');
        $dir = 'comprobantes/' . $c->fecha_emision->format('Y/m');
        if (!$disk->exists($dir)) $disk->makeDirectory($dir);
        $nombre = "{$cfg->ruc}-{$c->tipo}-{$c->serie}-" . str_pad((string) $c->numero, 8, '0', STR_PAD_LEFT) . $suffix;
        $ruta = $dir . '/' . $nombre;
        $disk->put($ruta, $contenido);
        return $ruta;
    }
}
