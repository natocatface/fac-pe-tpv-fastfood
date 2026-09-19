<?php

namespace App\Services\Sunat;

use App\Models\ComprobanteElectronico;
use App\Models\Configuracion;
use DOMDocument;

/**
 * Constructor del XML "Comunicación de Baja" (RA) y "Resumen Diario de Boletas" (RC)
 * según especificación SUNAT (UBL 2.0).
 *
 * Para anular Facturas / NC / ND se usa "Comunicación de Baja":
 *   - Tag: VoidedDocuments
 *   - Identificador: RA-{fecha}-{correlativo}
 *
 * Para anular Boletas se usa "Resumen Diario":
 *   - Tag: SummaryDocuments
 *   - Identificador: RC-{fecha}-{correlativo}
 */
class ResumenBajaXmlBuilder
{
    public const NS_VOIDED = 'urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1';
    public const NS_SAC    = 'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1';

    /**
     * Construye el XML de Comunicación de Baja para un comprobante.
     *
     * @param ComprobanteElectronico $c Comprobante a anular
     * @param int $correlativo Correlativo del día (1, 2, 3...)
     */
    public function construirComunicacionBaja(ComprobanteElectronico $c, int $correlativo = 1): array
    {
        $cfg = Configuracion::actual();
        $hoy = now()->format('Y-m-d');
        $fechaRef = $c->fecha_emision->format('Y-m-d');
        $idRA = 'RA-' . str_replace('-', '', $hoy) . '-' . str_pad((string) $correlativo, 5, '0', STR_PAD_LEFT);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;

        $root = $dom->createElementNS(self::NS_VOIDED, 'VoidedDocuments');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cac', UblXmlBuilder::NS_CAC);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cbc', UblXmlBuilder::NS_CBC);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds',  UblXmlBuilder::NS_DS);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ext', UblXmlBuilder::NS_EXT);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sac', self::NS_SAC);
        $dom->appendChild($root);

        // Extensiones (firma)
        $extensions = $dom->createElement('ext:UBLExtensions');
        $extension  = $dom->createElement('ext:UBLExtension');
        $extContent = $dom->createElement('ext:ExtensionContent');
        $extension->appendChild($extContent);
        $extensions->appendChild($extension);
        $root->appendChild($extensions);

        $this->cbc($dom, $root, 'UBLVersionID',    '2.0');
        $this->cbc($dom, $root, 'CustomizationID', '1.0');
        $this->cbc($dom, $root, 'ID',              $idRA);
        $this->cbc($dom, $root, 'ReferenceDate',   $fechaRef);
        $this->cbc($dom, $root, 'IssueDate',       $hoy);

        // Firma placeholder
        $this->firmaPlaceholder($dom, $root, $cfg);

        // Emisor
        $supplier = $dom->createElement('cac:AccountingSupplierParty');
        $this->cbcAttr($dom, $supplier, 'CustomerAssignedAccountID', $cfg->ruc, []);
        $this->cbc($dom, $supplier, 'AdditionalAccountID', '6');

        $party = $dom->createElement('cac:Party');
        $partyName = $dom->createElement('cac:PartyLegalEntity');
        $regName = $dom->createElement('cbc:RegistrationName');
        $regName->appendChild($dom->createCDATASection($cfg->razon_social ?: $cfg->nombre_empresa));
        $partyName->appendChild($regName);
        $party->appendChild($partyName);
        $supplier->appendChild($party);
        $root->appendChild($supplier);

        // Detalle del comprobante a anular
        $line = $dom->createElement('sac:VoidedDocumentsLine');
        $this->cbc($dom, $line, 'LineID', '1');
        $this->cbc($dom, $line, 'DocumentTypeCode', $c->tipo);
        $this->cbc($dom, $line, 'DocumentSerialID', $c->serie);
        $this->cbc($dom, $line, 'DocumentNumberID', (string) $c->numero);

        $voidReason = $dom->createElement('sac:VoidReasonDescription');
        $voidReason->appendChild($dom->createCDATASection($c->motivo_anulacion ?: 'Anulación del comprobante'));
        $line->appendChild($voidReason);
        $root->appendChild($line);

        return [
            'xml'          => $dom->saveXML(),
            'identificador'=> $idRA,
        ];
    }

    private function cbc(DOMDocument $dom, \DOMElement $parent, string $name, string $value): void
    {
        $el = $dom->createElement("cbc:{$name}", htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8'));
        $parent->appendChild($el);
    }

    private function cbcAttr(DOMDocument $dom, \DOMElement $parent, string $name, string $value, array $attrs): void
    {
        $el = $dom->createElement("cbc:{$name}", $value);
        foreach ($attrs as $k => $v) $el->setAttribute($k, $v);
        $parent->appendChild($el);
    }

    private function firmaPlaceholder(DOMDocument $dom, \DOMElement $root, Configuracion $cfg): void
    {
        $sig = $dom->createElement('cac:Signature');
        $this->cbc($dom, $sig, 'ID', $cfg->ruc);

        $party = $dom->createElement('cac:SignatoryParty');
        $partyId = $dom->createElement('cac:PartyIdentification');
        $this->cbc($dom, $partyId, 'ID', $cfg->ruc);
        $party->appendChild($partyId);

        $partyName = $dom->createElement('cac:PartyName');
        $name = $dom->createElement('cbc:Name');
        $name->appendChild($dom->createCDATASection($cfg->razon_social ?: $cfg->nombre_empresa));
        $partyName->appendChild($name);
        $party->appendChild($partyName);
        $sig->appendChild($party);

        $digSig = $dom->createElement('cac:DigitalSignatureAttachment');
        $extRef = $dom->createElement('cac:ExternalReference');
        $this->cbc($dom, $extRef, 'URI', "#{$cfg->ruc}-SignatureFFFF");
        $digSig->appendChild($extRef);
        $sig->appendChild($digSig);

        $root->appendChild($sig);
    }
}
