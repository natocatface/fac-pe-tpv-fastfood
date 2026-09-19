<?php

namespace App\Services\Sunat;

use App\Models\Configuracion;
use DOMDocument;
use DOMXPath;

/**
 * Firma digital XMLDSig sobre el XML UBL 2.1.
 *
 * Implementación nativa de XML-Signature Syntax and Processing (W3C):
 *   https://www.w3.org/TR/xmldsig-core/
 *
 * Funciona con certificados X.509 en formato .pem o .pfx (PKCS#12).
 *
 * Si tienes problemas con OpenSSL para tu certificado, alternativamente
 * puedes integrar robrichards/xmlseclibs.
 */
class FirmaDigitalService
{
    /**
     * Firma el XML in-place. Devuelve el XML firmado.
     *
     * @return array{xml:string, digest:string}
     */
    public function firmar(string $xml): array
    {
        $cfg = Configuracion::actual();

        if (!$cfg->certificado_path) {
            throw new \RuntimeException('No se ha configurado un certificado digital. Sube tu .pem en Configuración → SUNAT.');
        }

        $certPath = storage_path('app/' . $cfg->certificado_path);
        if (!file_exists($certPath)) {
            $certPath = $cfg->certificado_path;
        }
        if (!file_exists($certPath)) {
            throw new \RuntimeException("Certificado no encontrado: {$certPath}");
        }

        $contenidoCert = file_get_contents($certPath);
        $password = $cfg->certificado_password ?: '';

        [$privateKey, $publicCert] = $this->extraerLlaves($contenidoCert, $password, $certPath);

        // Cargar el DOM
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);

        // ============ 1. Calcular digest del documento (sin la firma) ============
        $canonicalDoc = $dom->C14N(false, false);
        $digestValue  = base64_encode(hash('sha1', $canonicalDoc, true));

        // ============ 2. Construir SignedInfo ============
        $signedInfoXml = '<ds:SignedInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">'
            . '<ds:CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>'
            . '<ds:SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>'
            . '<ds:Reference URI="">'
            .   '<ds:Transforms>'
            .     '<ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>'
            .   '</ds:Transforms>'
            .   '<ds:DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>'
            .   '<ds:DigestValue>' . $digestValue . '</ds:DigestValue>'
            . '</ds:Reference>'
            . '</ds:SignedInfo>';

        // Canonicalizar SignedInfo para firmar
        $tmp = new DOMDocument();
        $tmp->loadXML($signedInfoXml);
        $signedInfoC14n = $tmp->documentElement->C14N(false, false);

        // ============ 3. Firmar SignedInfo con la clave privada ============
        $signatureBytes = '';
        if (!openssl_sign($signedInfoC14n, $signatureBytes, $privateKey, OPENSSL_ALGO_SHA1)) {
            throw new \RuntimeException('Error al firmar el XML: ' . openssl_error_string());
        }
        $signatureValue = base64_encode($signatureBytes);

        // ============ 4. Extraer el certificado público en base64 ============
        $certBase64 = preg_replace('/-+BEGIN CERTIFICATE-+|-+END CERTIFICATE-+|\s/', '', $publicCert);

        // ============ 5. Construir el nodo ds:Signature completo ============
        $signatureXml = '<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#" Id="SignatureSP">'
            . $signedInfoXml
            . '<ds:SignatureValue>' . $signatureValue . '</ds:SignatureValue>'
            . '<ds:KeyInfo>'
            .   '<ds:X509Data><ds:X509Certificate>' . $certBase64 . '</ds:X509Certificate></ds:X509Data>'
            . '</ds:KeyInfo>'
            . '</ds:Signature>';

        // ============ 6. Insertar firma dentro de ext:ExtensionContent ============
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('ext', UblXmlBuilder::NS_EXT);
        $extContent = $xpath->query('//ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent')->item(0);
        if (!$extContent) {
            throw new \RuntimeException('No se encontró ext:ExtensionContent para insertar la firma.');
        }

        $signatureFragment = $dom->createDocumentFragment();
        $signatureFragment->appendXML($signatureXml);
        $extContent->appendChild($signatureFragment);

        return [
            'xml'    => $dom->saveXML(),
            'digest' => $digestValue,
        ];
    }

    /**
     * Extrae la llave privada y el certificado público del archivo (PEM o PFX).
     *
     * @return array{0: \OpenSSLAsymmetricKey, 1: string}
     */
    private function extraerLlaves(string $contenido, string $password, string $path)
    {
        // PFX (PKCS12 binario)
        if (str_ends_with(strtolower($path), '.pfx') || str_ends_with(strtolower($path), '.p12')) {
            if (!openssl_pkcs12_read($contenido, $certs, $password)) {
                throw new \RuntimeException('No se pudo abrir el .pfx. Verifica la contraseña.');
            }
            $pk = openssl_pkey_get_private($certs['pkey']);
            return [$pk, $certs['cert']];
        }

        // PEM (texto)
        $pk = openssl_pkey_get_private($contenido, $password);
        if (!$pk) {
            throw new \RuntimeException('No se pudo extraer la clave privada del .pem. ' . openssl_error_string());
        }

        // Extraer el bloque de certificado
        if (preg_match('/-+BEGIN CERTIFICATE-+.+?-+END CERTIFICATE-+/s', $contenido, $m)) {
            return [$pk, $m[0]];
        }

        throw new \RuntimeException('El archivo no contiene un certificado público válido.');
    }
}
