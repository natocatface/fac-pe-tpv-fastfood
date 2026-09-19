<?php

namespace App\Services\Sunat;

use App\Models\Configuracion;
use SoapClient;
use ZipArchive;

/**
 * Cliente SOAP para el servicio billService de SUNAT.
 *
 * Endpoints:
 *  - Beta:        https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService
 *  - Producción:  https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService
 *
 * Métodos SOAP usados:
 *  - sendBill: envía Factura / Boleta
 *  - sendSummary: envía resumen diario de boletas
 *  - getStatus: consulta estado de un ticket
 *
 * Doc: https://cpe.sunat.gob.pe/sites/default/files/inline-files/ws.pdf
 */
class SunatSoapClient
{
    public const ENDPOINT_BETA       = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl';
    public const ENDPOINT_PRODUCCION = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService?wsdl';

    private string $endpoint;
    private string $usuario;  // RUC + USUARIO SOL
    private string $clave;

    public function __construct(?Configuracion $cfg = null)
    {
        $cfg = $cfg ?: Configuracion::actual();
        $this->endpoint = $cfg->sunat_modo === 'produccion'
            ? self::ENDPOINT_PRODUCCION
            : self::ENDPOINT_BETA;
        $this->usuario = $cfg->ruc . $cfg->usuario_sol;
        $this->clave   = $cfg->clave_sol;
    }

    /**
     * Envía un comprobante (Factura/Boleta/NC/ND) a SUNAT.
     *
     * @return array{ok:bool, codigo?:string, mensaje?:string, cdr_zip_b64?:string, cdr_xml?:string, hash?:string, observaciones?:array}
     */
    public function enviarComprobante(string $nombreSinExt, string $xmlFirmado): array
    {
        $zipB64 = $this->empaquetarZipBase64($nombreSinExt, $xmlFirmado);

        try {
            $client = $this->crearClienteSoap();

            $response = $client->sendBill([
                'fileName'    => $nombreSinExt . '.zip',
                'contentFile' => $zipB64,
            ]);

            $applicationResponseB64 = $response->applicationResponse ?? null;
            if (!$applicationResponseB64) {
                return ['ok' => false, 'mensaje' => 'SUNAT no devolvió applicationResponse.'];
            }

            // CDR (Constancia de Recepción) es un ZIP con el XML de respuesta dentro
            $cdrXml = $this->extraerCdrDelZip(base64_decode($applicationResponseB64));
            $info   = $this->parsearCdr($cdrXml);

            return array_merge([
                'ok' => true,
                'cdr_zip_b64' => $applicationResponseB64,
                'cdr_xml'     => $cdrXml,
            ], $info);
        } catch (\SoapFault $e) {
            return [
                'ok'      => false,
                'codigo'  => $this->codigoDeFault($e),
                'mensaje' => $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            return [
                'ok'      => false,
                'mensaje' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Envía Comunicación de Baja o Resumen Diario (método asíncrono sendSummary).
     * Devuelve un ticket que se consulta posteriormente con getStatus.
     */
    public function enviarResumenBaja(string $nombreSinExt, string $xmlFirmado): array
    {
        $zipB64 = $this->empaquetarZipBase64($nombreSinExt, $xmlFirmado);

        try {
            $client = $this->crearClienteSoap();
            $response = $client->sendSummary([
                'fileName'    => $nombreSinExt . '.zip',
                'contentFile' => $zipB64,
            ]);
            $ticket = $response->ticket ?? null;
            if (!$ticket) {
                return ['ok' => false, 'mensaje' => 'SUNAT no devolvió ticket.'];
            }
            return ['ok' => true, 'ticket' => $ticket];
        } catch (\SoapFault $e) {
            return ['ok' => false, 'codigo' => $this->codigoDeFault($e), 'mensaje' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'mensaje' => $e->getMessage()];
        }
    }

    /**
     * Consulta el estado de un envío asíncrono (Comunicación de Baja / Resumen).
     */
    public function consultarTicket(string $ticket): array
    {
        try {
            $client = $this->crearClienteSoap();
            $response = $client->getStatus(['ticket' => $ticket]);
            $status = $response->status ?? null;
            if (!$status) return ['ok' => false, 'mensaje' => 'Sin respuesta'];

            $cdrXml = '';
            if (!empty($status->content)) {
                $cdrXml = $this->extraerCdrDelZip(base64_decode($status->content));
            }
            return [
                'ok'           => true,
                'codigo'       => $status->statusCode ?? '',
                'mensaje'      => $status->statusMessage ?? '',
                'cdr_xml'      => $cdrXml,
                'cdr_zip_b64'  => $status->content ?? null,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'mensaje' => $e->getMessage()];
        }
    }

    /** Empaqueta el XML firmado en un ZIP y devuelve base64. */
    private function empaquetarZipBase64(string $nombreSinExt, string $xmlFirmado): string
    {
        $tmpZip = tempnam(sys_get_temp_dir(), 'sunat_') . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($tmpZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el ZIP para SUNAT.');
        }
        $zip->addFromString($nombreSinExt . '.xml', $xmlFirmado);
        $zip->close();

        $b64 = base64_encode(file_get_contents($tmpZip));
        @unlink($tmpZip);
        return $b64;
    }

    /** Extrae el XML CDR (R-...xml) desde el ZIP devuelto por SUNAT. */
    private function extraerCdrDelZip(string $zipBinario): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'cdr_') . '.zip';
        file_put_contents($tmp, $zipBinario);
        $zip = new ZipArchive();
        $cdrXml = '';
        if ($zip->open($tmp) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_ends_with(strtolower($name), '.xml')) {
                    $cdrXml = $zip->getFromIndex($i);
                    break;
                }
            }
            $zip->close();
        }
        @unlink($tmp);
        return $cdrXml;
    }

    /** Parsea el CDR XML para extraer código, descripción y observaciones. */
    private function parsearCdr(string $cdrXml): array
    {
        if (!$cdrXml) return ['codigo' => '', 'mensaje' => 'CDR vacío'];

        $xml = simplexml_load_string($cdrXml);
        if (!$xml) return ['codigo' => '', 'mensaje' => 'CDR no parseable'];

        $xml->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->registerXPathNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        $codigo = (string) (($xml->xpath('//cac:DocumentResponse/cac:Response/cbc:ResponseCode')[0] ?? '') ?: '');
        $desc   = (string) (($xml->xpath('//cac:DocumentResponse/cac:Response/cbc:Description')[0]  ?? '') ?: '');

        $obs = [];
        foreach ($xml->xpath('//cac:DocumentResponse/cac:Response/cbc:Description') ?: [] as $i => $d) {
            if ($i === 0) continue;
            $obs[] = (string) $d;
        }

        return [
            'codigo'  => $codigo,
            'mensaje' => $desc,
            'observaciones' => $obs,
        ];
    }

    private function codigoDeFault(\SoapFault $e): string
    {
        if (preg_match('/(\d{4})/', $e->faultcode ?? '', $m)) return $m[1];
        return $e->faultcode ?? '';
    }

    private function crearClienteSoap(): SoapClient
    {
        $opts = [
            'soap_version' => SOAP_1_1,
            'login'        => $this->usuario,
            'password'     => $this->clave,
            'trace'        => 1,
            'exceptions'   => true,
            'cache_wsdl'   => WSDL_CACHE_NONE,
            'connection_timeout' => 30,
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]),
        ];

        return new SoapClient($this->endpoint, $opts);
    }
}
