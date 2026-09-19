<?php

namespace App\Services\Sunat;

use App\Models\ComprobanteElectronico;
use App\Models\Configuracion;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

/**
 * Generación del PDF (representación impresa) del comprobante electrónico
 * cumpliendo el Anexo 8 de SUNAT.
 *
 * Incluye código QR según Anexo 7:
 *   RUC|Tipo|Serie|Numero|IGV|Total|FechaEmision|TipoDocReceptor|NumDocReceptor|HashFirma
 */
class ComprobantePdfService
{
    /** Genera el PDF y lo devuelve como string binario. */
    public function generar(ComprobanteElectronico $c): string
    {
        $cfg = Configuracion::actual();
        $c->loadMissing('lineas');

        $qrTexto    = $this->generarTextoQR($c, $cfg);
        $qrBase64   = $this->generarQRBase64($qrTexto);

        $pdf = Pdf::loadView('comprobantes.pdf', [
            'comprobante' => $c,
            'config'      => $cfg,
            'qrBase64'    => $qrBase64,
            'qrTexto'     => $qrTexto,
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /** Genera y guarda el PDF en disco asociándolo al comprobante. */
    public function generarYGuardar(ComprobanteElectronico $c): string
    {
        $cfg = Configuracion::actual();
        $disk = Storage::disk('local');
        $dir  = 'comprobantes/' . $c->fecha_emision->format('Y/m');
        if (!$disk->exists($dir)) $disk->makeDirectory($dir);

        $nombre = "{$cfg->ruc}-{$c->tipo}-{$c->serie}-" . str_pad((string) $c->numero, 8, '0', STR_PAD_LEFT) . '.pdf';
        $ruta = $dir . '/' . $nombre;

        $contenido = $this->generar($c);
        $disk->put($ruta, $contenido);
        $c->update(['pdf_path' => $ruta]);

        return $ruta;
    }

    /**
     * Texto del código QR según Anexo 7 SUNAT:
     *   RUC|Tipo|Serie|Numero|MtoIGV|MtoTotal|FechaEmision|TipoDocReceptor|NumDocReceptor|HashFirma
     */
    public function generarTextoQR(ComprobanteElectronico $c, Configuracion $cfg): string
    {
        return implode('|', [
            $cfg->ruc,
            $c->tipo,
            $c->serie,
            (string) $c->numero,
            number_format($c->igv, 2, '.', ''),
            number_format($c->total, 2, '.', ''),
            $c->fecha_emision->format('Y-m-d'),
            $c->tipo_doc_receptor,
            $c->num_doc_receptor,
            $c->hash_xml ?? '',
        ]);
    }

    /**
     * Genera un PNG QR en base64 listo para `<img src="data:image/png;base64,...">`.
     *
     * Estrategia en cascada (usa la primera que funcione):
     *   1) chillerlan/php-qrcode si está instalado vía composer (mejor opción, offline).
     *   2) API pública api.qrserver.com (no requiere dependencia local pero sí internet).
     *   3) Endroid/qr-code si está disponible.
     *   4) Imagen GD simple con el texto (último recurso para no dejar el PDF vacío).
     */
    public function generarQRBase64(string $texto): string
    {
        // 1) chillerlan/php-qrcode (la dependencia declarada en composer.json)
        if (class_exists(QRCode::class)) {
            try {
                $options = new QROptions([
                    'version'    => 5,
                    'eccLevel'   => QRCode::ECC_M,
                    'scale'      => 4,
                    'imageBase64'=> false,
                    // En v5 outputType ya no se usa, pero lo dejamos por compatibilidad:
                    'outputType' => defined(QRCode::class.'::OUTPUT_IMAGE_PNG')
                        ? QRCode::OUTPUT_IMAGE_PNG
                        : 'png',
                ]);
                $png = (new QRCode($options))->render($texto);
                if ($png) return base64_encode($png);
            } catch (\Throwable $e) {
                \Log::warning('chillerlan QR falló: '.$e->getMessage());
            }
        }

        // 2) API pública qrserver.com (estable, gratuita, hace ~10 años activa)
        try {
            $url = 'https://api.qrserver.com/v1/create-qr-code/?'
                . http_build_query([
                    'size'   => '200x200',
                    'data'   => $texto,
                    'ecc'    => 'M',
                    'format' => 'png',
                    'margin' => '0',
                ]);
            $ctx = stream_context_create([
                'http' => ['timeout' => 4, 'header' => "User-Agent: TPVFastFood/1.0\r\n"],
                'https'=> ['timeout' => 4, 'header' => "User-Agent: TPVFastFood/1.0\r\n"],
            ]);
            $png = @file_get_contents($url, false, $ctx);
            if ($png !== false && strlen($png) > 100) {
                return base64_encode($png);
            }
        } catch (\Throwable $e) {
            \Log::warning('qrserver.com QR falló: '.$e->getMessage());
        }

        // 3) endroid/qr-code (si en el futuro se instala como alternativa)
        if (class_exists(\Endroid\QrCode\Builder\Builder::class)) {
            try {
                $result = \Endroid\QrCode\Builder\Builder::create()
                    ->data($texto)
                    ->size(200)
                    ->margin(0)
                    ->build();
                return base64_encode($result->getString());
            } catch (\Throwable $e) {
                \Log::warning('Endroid QR falló: '.$e->getMessage());
            }
        }

        // 4) Último recurso: imagen GD con el texto del QR
        return $this->generarQRFallbackGD($texto);
    }

    /**
     * Genera un PNG de aviso usando GD cuando ninguna librería de QR está disponible.
     * Permite que el PDF se vea bien aunque sin QR escaneable.
     */
    private function generarQRFallbackGD(string $texto): string
    {
        if (!function_exists('imagecreate')) {
            return '';
        }
        $img = imagecreate(200, 200);
        $bg     = imagecolorallocate($img, 245, 245, 245);
        $border = imagecolorallocate($img, 200, 200, 200);
        $dark   = imagecolorallocate($img, 80, 80, 80);
        imagefilledrectangle($img, 0, 0, 199, 199, $bg);
        imagerectangle($img, 0, 0, 199, 199, $border);
        imagestring($img, 4, 20, 70,  'QR no', $dark);
        imagestring($img, 4, 18, 95,  'disponible', $dark);
        imagestring($img, 2, 10, 130, '(falta librería)', $dark);

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);
        return base64_encode($png);
    }
}
