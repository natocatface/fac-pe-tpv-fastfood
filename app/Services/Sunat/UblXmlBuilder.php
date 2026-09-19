<?php

namespace App\Services\Sunat;

use App\Models\ComprobanteElectronico;
use App\Models\Configuracion;
use DOMDocument;

/**
 * Constructor de XML UBL 2.1 para SUNAT (Perú).
 *
 * Genera Factura (01) y Boleta (03) cumpliendo:
 * - UBL Versión 2.1
 * - Customization ID 2.0 (SUNAT)
 * - Catálogos SUNAT vigentes (01, 06, 07, 09, 10, 12, etc.)
 *
 * Referencia oficial: https://cpe.sunat.gob.pe/node/88
 */
class UblXmlBuilder
{
    // Namespaces UBL
    public const NS_INVOICE      = 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2';
    public const NS_CREDIT       = 'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2';
    public const NS_DEBIT        = 'urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2';
    public const NS_CAC          = 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2';
    public const NS_CBC          = 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2';
    public const NS_DS           = 'http://www.w3.org/2000/09/xmldsig#';
    public const NS_EXT          = 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2';
    public const NS_QDT          = 'urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2';
    public const NS_UDT          = 'urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2';
    public const NS_SAC          = 'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1';

    public function construir(ComprobanteElectronico $c): string
    {
        $cfg = Configuracion::actual();
        $c->loadMissing('lineas');

        $isNotaCredito = $c->esNotaCredito();
        $isNotaDebito  = $c->esNotaDebito();

        $rootName = $isNotaCredito ? 'CreditNote' : ($isNotaDebito ? 'DebitNote' : 'Invoice');
        $rootNS   = $isNotaCredito ? self::NS_CREDIT : ($isNotaDebito ? self::NS_DEBIT : self::NS_INVOICE);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;
        $dom->preserveWhiteSpace = false;

        $root = $dom->createElementNS($rootNS, $rootName);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cac', self::NS_CAC);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:cbc', self::NS_CBC);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ds',  self::NS_DS);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:ext', self::NS_EXT);
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:sac', self::NS_SAC);
        $dom->appendChild($root);

        // ============ Extensiones (lugar donde se inserta la firma digital) ============
        $extensions = $dom->createElement('ext:UBLExtensions');
        $extension  = $dom->createElement('ext:UBLExtension');
        $extContent = $dom->createElement('ext:ExtensionContent');
        $extension->appendChild($extContent);
        $extensions->appendChild($extension);
        $root->appendChild($extensions);

        // ============ Datos generales ============
        $this->addCbc($dom, $root, 'UBLVersionID',    '2.1');
        $this->addCbc($dom, $root, 'CustomizationID', '2.0');
        $this->addCbc($dom, $root, 'ID',              $c->numero_completo);
        $this->addCbc($dom, $root, 'IssueDate',       $c->fecha_emision->format('Y-m-d'));
        if ($c->hora_emision) {
            $this->addCbc($dom, $root, 'IssueTime',   $c->hora_emision);
        }
        if ($c->fecha_vencimiento) {
            $this->addCbc($dom, $root, 'DueDate',     $c->fecha_vencimiento->format('Y-m-d'));
        }

        // Tipo de comprobante (Factura/Boleta usan InvoiceTypeCode, NC/ND no)
        if (!$isNotaCredito && !$isNotaDebito) {
            $tipoNode = $dom->createElement('cbc:InvoiceTypeCode', $c->tipo);
            $tipoNode->setAttribute('listID', '0101'); // Operación interna
            $tipoNode->setAttribute('listAgencyName', 'PE:SUNAT');
            $tipoNode->setAttribute('listName', 'Tipo de Documento');
            $tipoNode->setAttribute('listURI', 'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01');
            $tipoNode->setAttribute('name', 'Tipo de Operación'); // 0101 = Venta interna
            $root->appendChild($tipoNode);
        }

        // Total en letras (obligatorio SUNAT)
        if ($c->total_letras) {
            $note = $dom->createElement('cbc:Note', $this->escape($c->total_letras));
            $note->setAttribute('languageLocaleID', '1000');
            $root->appendChild($note);
        }

        $this->addCbc($dom, $root, 'DocumentCurrencyCode', $c->moneda);

        // Si es NC/ND: referencia al documento que modifica
        if ($isNotaCredito || $isNotaDebito) {
            $this->agregarDiscrepancia($dom, $root, $c);
            $this->agregarReferenciaDoc($dom, $root, $c);
        }

        // ============ Firma (DigitalSignature placeholder) ============
        $this->agregarFirmaPlaceholder($dom, $root, $cfg);

        // ============ Emisor (Supplier) ============
        $this->agregarEmisor($dom, $root, $cfg);

        // ============ Receptor (Customer) ============
        $this->agregarReceptor($dom, $root, $c);

        // ============ Totales de impuestos ============
        $this->agregarTaxTotal($dom, $root, $c);

        // ============ Importes monetarios ============
        $monetaryTag = $isNotaCredito ? 'cac:LegalMonetaryTotal' : ($isNotaDebito ? 'cac:RequestedMonetaryTotal' : 'cac:LegalMonetaryTotal');
        $monetary = $dom->createElement($monetaryTag);
        $this->addCbcAmount($dom, $monetary, 'LineExtensionAmount', $c->total - $c->igv, $c->moneda);
        $this->addCbcAmount($dom, $monetary, 'TaxInclusiveAmount',  $c->total,           $c->moneda);
        if ($c->total_descuento > 0) {
            $this->addCbcAmount($dom, $monetary, 'AllowanceTotalAmount', $c->total_descuento, $c->moneda);
        }
        $this->addCbcAmount($dom, $monetary, 'PayableAmount',       $c->total,           $c->moneda);
        $root->appendChild($monetary);

        // ============ Líneas (InvoiceLine / CreditNoteLine / DebitNoteLine) ============
        $lineTag = $isNotaCredito ? 'cac:CreditNoteLine' : ($isNotaDebito ? 'cac:DebitNoteLine' : 'cac:InvoiceLine');
        $qtyTag  = $isNotaCredito ? 'cbc:CreditedQuantity' : ($isNotaDebito ? 'cbc:DebitedQuantity' : 'cbc:InvoicedQuantity');
        foreach ($c->lineas as $linea) {
            $root->appendChild($this->construirLinea($dom, $linea, $c->moneda, $lineTag, $qtyTag));
        }

        return $dom->saveXML();
    }

    // ===========================================================
    //                    Helpers privados
    // ===========================================================

    private function agregarFirmaPlaceholder(DOMDocument $dom, \DOMElement $root, Configuracion $cfg): void
    {
        $sig = $dom->createElement('cac:Signature');
        $this->addCbc($dom, $sig, 'ID', $cfg->ruc);

        $party = $dom->createElement('cac:SignatoryParty');
        $partyId = $dom->createElement('cac:PartyIdentification');
        $this->addCbc($dom, $partyId, 'ID', $cfg->ruc);
        $party->appendChild($partyId);

        $partyName = $dom->createElement('cac:PartyName');
        $name = $dom->createElement('cbc:Name');
        $name->appendChild($dom->createCDATASection($cfg->razon_social ?: $cfg->nombre_empresa));
        $partyName->appendChild($name);
        $party->appendChild($partyName);
        $sig->appendChild($party);

        $digSig = $dom->createElement('cac:DigitalSignatureAttachment');
        $extRef = $dom->createElement('cac:ExternalReference');
        $this->addCbc($dom, $extRef, 'URI', "#{$cfg->ruc}-SignatureFFFF");
        $digSig->appendChild($extRef);
        $sig->appendChild($digSig);
        $root->appendChild($sig);
    }

    private function agregarEmisor(DOMDocument $dom, \DOMElement $root, Configuracion $cfg): void
    {
        $supplier = $dom->createElement('cac:AccountingSupplierParty');
        $party = $dom->createElement('cac:Party');

        $partyId = $dom->createElement('cac:PartyIdentification');
        $id = $dom->createElement('cbc:ID', $cfg->ruc);
        $id->setAttribute('schemeID', '6'); // 6 = RUC
        $id->setAttribute('schemeName', 'Documento de Identidad');
        $id->setAttribute('schemeAgencyName', 'PE:SUNAT');
        $id->setAttribute('schemeURI', 'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06');
        $partyId->appendChild($id);
        $party->appendChild($partyId);

        $partyName = $dom->createElement('cac:PartyName');
        $name = $dom->createElement('cbc:Name');
        $name->appendChild($dom->createCDATASection($cfg->nombre_empresa));
        $partyName->appendChild($name);
        $party->appendChild($partyName);

        $legalEntity = $dom->createElement('cac:PartyLegalEntity');
        $regName = $dom->createElement('cbc:RegistrationName');
        $regName->appendChild($dom->createCDATASection($cfg->razon_social ?: $cfg->nombre_empresa));
        $legalEntity->appendChild($regName);

        $regAddr = $dom->createElement('cac:RegistrationAddress');
        if ($cfg->ubigeo) $this->addCbc($dom, $regAddr, 'ID', $cfg->ubigeo);
        $this->addCbc($dom, $regAddr, 'AddressTypeCode', '0000');
        if ($cfg->ciudad)       $this->addCbc($dom, $regAddr, 'CityName',          $cfg->ciudad);
        if ($cfg->provincia)    $this->addCbc($dom, $regAddr, 'CountrySubentity',  $cfg->provincia);
        if ($cfg->distrito)     $this->addCbc($dom, $regAddr, 'District',          $cfg->distrito);

        $addrLine = $dom->createElement('cac:AddressLine');
        $line = $dom->createElement('cbc:Line');
        $line->appendChild($dom->createCDATASection($cfg->direccion ?: '-'));
        $addrLine->appendChild($line);
        $regAddr->appendChild($addrLine);

        $country = $dom->createElement('cac:Country');
        $countryCode = $dom->createElement('cbc:IdentificationCode', 'PE');
        $countryCode->setAttribute('listID', 'ISO 3166-1');
        $countryCode->setAttribute('listAgencyName', 'United Nations Economic Commission for Europe');
        $countryCode->setAttribute('listName', 'Country');
        $country->appendChild($countryCode);
        $regAddr->appendChild($country);

        $legalEntity->appendChild($regAddr);
        $party->appendChild($legalEntity);

        $supplier->appendChild($party);
        $root->appendChild($supplier);
    }

    private function agregarReceptor(DOMDocument $dom, \DOMElement $root, ComprobanteElectronico $c): void
    {
        $customer = $dom->createElement('cac:AccountingCustomerParty');
        $party = $dom->createElement('cac:Party');

        $partyId = $dom->createElement('cac:PartyIdentification');
        $id = $dom->createElement('cbc:ID', $c->num_doc_receptor);
        $id->setAttribute('schemeID', $c->tipo_doc_receptor);
        $id->setAttribute('schemeName', 'Documento de Identidad');
        $id->setAttribute('schemeAgencyName', 'PE:SUNAT');
        $id->setAttribute('schemeURI', 'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06');
        $partyId->appendChild($id);
        $party->appendChild($partyId);

        $legalEntity = $dom->createElement('cac:PartyLegalEntity');
        $regName = $dom->createElement('cbc:RegistrationName');
        $regName->appendChild($dom->createCDATASection($c->razon_social_receptor));
        $legalEntity->appendChild($regName);

        if ($c->direccion_receptor) {
            $regAddr = $dom->createElement('cac:RegistrationAddress');
            $addrLine = $dom->createElement('cac:AddressLine');
            $line = $dom->createElement('cbc:Line');
            $line->appendChild($dom->createCDATASection($c->direccion_receptor));
            $addrLine->appendChild($line);
            $regAddr->appendChild($addrLine);
            $legalEntity->appendChild($regAddr);
        }
        $party->appendChild($legalEntity);

        $customer->appendChild($party);
        $root->appendChild($customer);
    }

    private function agregarTaxTotal(DOMDocument $dom, \DOMElement $root, ComprobanteElectronico $c): void
    {
        $taxTotal = $dom->createElement('cac:TaxTotal');
        $this->addCbcAmount($dom, $taxTotal, 'TaxAmount', $c->igv, $c->moneda);

        // Subtotal IGV
        $taxSubtotal = $dom->createElement('cac:TaxSubtotal');
        $this->addCbcAmount($dom, $taxSubtotal, 'TaxableAmount', $c->total_gravado, $c->moneda);
        $this->addCbcAmount($dom, $taxSubtotal, 'TaxAmount',     $c->igv,           $c->moneda);

        $cat = $dom->createElement('cac:TaxCategory');
        $taxScheme = $dom->createElement('cac:TaxScheme');
        $tsId = $dom->createElement('cbc:ID', '1000');
        $tsId->setAttribute('schemeName', 'Codigo de tributos');
        $tsId->setAttribute('schemeAgencyName', 'PE:SUNAT');
        $tsId->setAttribute('schemeURI', 'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05');
        $taxScheme->appendChild($tsId);
        $this->addCbc($dom, $taxScheme, 'Name', 'IGV');
        $this->addCbc($dom, $taxScheme, 'TaxTypeCode', 'VAT');
        $cat->appendChild($taxScheme);
        $taxSubtotal->appendChild($cat);

        $taxTotal->appendChild($taxSubtotal);
        $root->appendChild($taxTotal);
    }

    private function agregarDiscrepancia(DOMDocument $dom, \DOMElement $root, ComprobanteElectronico $c): void
    {
        $disc = $dom->createElement('cac:DiscrepancyResponse');
        $this->addCbc($dom, $disc, 'ReferenceID', $c->doc_modificado_numero);
        $this->addCbc($dom, $disc, 'ResponseCode', $c->codigo_motivo_nota ?: '01');
        $descNode = $dom->createElement('cbc:Description');
        $descNode->appendChild($dom->createCDATASection($c->motivo_anulacion ?: 'Anulación del comprobante'));
        $disc->appendChild($descNode);
        $root->appendChild($disc);
    }

    private function agregarReferenciaDoc(DOMDocument $dom, \DOMElement $root, ComprobanteElectronico $c): void
    {
        $ref = $dom->createElement('cac:BillingReference');
        $invRef = $dom->createElement('cac:InvoiceDocumentReference');
        $this->addCbc($dom, $invRef, 'ID', $c->doc_modificado_numero);
        $tipoDocRef = $dom->createElement('cbc:DocumentTypeCode', $c->doc_modificado_tipo ?: '01');
        $tipoDocRef->setAttribute('listAgencyName', 'PE:SUNAT');
        $tipoDocRef->setAttribute('listName', 'Tipo de Documento');
        $tipoDocRef->setAttribute('listURI', 'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01');
        $invRef->appendChild($tipoDocRef);
        $ref->appendChild($invRef);
        $root->appendChild($ref);
    }

    private function construirLinea(DOMDocument $dom, $linea, string $moneda, string $lineTag, string $qtyTag): \DOMElement
    {
        $node = $dom->createElement($lineTag);
        $this->addCbc($dom, $node, 'ID', (string) $linea->orden);

        // Cantidad
        $qty = $dom->createElement($qtyTag, number_format($linea->cantidad, 2, '.', ''));
        $qty->setAttribute('unitCode', $linea->unidad_medida ?? 'NIU');
        $qty->setAttribute('unitCodeListID', 'UN/ECE rec 20');
        $qty->setAttribute('unitCodeListAgencyName', 'United Nations Economic Commission for Europe');
        $node->appendChild($qty);

        // Valor de venta (sin IGV)
        $this->addCbcAmount($dom, $node, 'LineExtensionAmount', $linea->valor_total, $moneda);

        // Precios alternativos (precio con IGV - tipo 01 referencial)
        $pricing = $dom->createElement('cac:PricingReference');
        $altPrice = $dom->createElement('cac:AlternativeConditionPrice');
        $this->addCbcAmount($dom, $altPrice, 'PriceAmount', $linea->precio_unitario, $moneda);
        $this->addCbc($dom, $altPrice, 'PriceTypeCode', '01'); // Precio unitario incluye IGV
        $pricing->appendChild($altPrice);
        $node->appendChild($pricing);

        // Tax total de la línea
        $taxTotal = $dom->createElement('cac:TaxTotal');
        $this->addCbcAmount($dom, $taxTotal, 'TaxAmount', $linea->igv, $moneda);
        $taxSub = $dom->createElement('cac:TaxSubtotal');
        $this->addCbcAmount($dom, $taxSub, 'TaxableAmount', $linea->valor_total, $moneda);
        $this->addCbcAmount($dom, $taxSub, 'TaxAmount',     $linea->igv,         $moneda);

        $cat = $dom->createElement('cac:TaxCategory');
        $porc = ($linea->valor_total > 0) ? round(($linea->igv / $linea->valor_total) * 100, 2) : 0;
        $this->addCbc($dom, $cat, 'Percent', (string) $porc);
        $this->addCbc($dom, $cat, 'TaxExemptionReasonCode', $linea->tipo_afectacion_igv ?? '10');

        $taxScheme = $dom->createElement('cac:TaxScheme');
        $this->addCbc($dom, $taxScheme, 'ID', '1000');
        $this->addCbc($dom, $taxScheme, 'Name', 'IGV');
        $this->addCbc($dom, $taxScheme, 'TaxTypeCode', 'VAT');
        $cat->appendChild($taxScheme);
        $taxSub->appendChild($cat);
        $taxTotal->appendChild($taxSub);
        $node->appendChild($taxTotal);

        // Item (descripción y código del producto)
        $item = $dom->createElement('cac:Item');
        $itemDesc = $dom->createElement('cbc:Description');
        $itemDesc->appendChild($dom->createCDATASection($linea->descripcion));
        $item->appendChild($itemDesc);

        if ($linea->codigo_producto) {
            $sellerId = $dom->createElement('cac:SellersItemIdentification');
            $this->addCbc($dom, $sellerId, 'ID', $linea->codigo_producto);
            $item->appendChild($sellerId);
        }
        $node->appendChild($item);

        // Precio unitario sin IGV (Price)
        $price = $dom->createElement('cac:Price');
        $this->addCbcAmount($dom, $price, 'PriceAmount', $linea->valor_unitario, $moneda);
        $node->appendChild($price);

        return $node;
    }

    private function addCbc(DOMDocument $dom, \DOMElement $parent, string $name, string $value): void
    {
        $el = $dom->createElement("cbc:{$name}", $this->escape($value));
        $parent->appendChild($el);
    }

    private function addCbcAmount(DOMDocument $dom, \DOMElement $parent, string $name, float $value, string $currency): void
    {
        $el = $dom->createElement("cbc:{$name}", number_format($value, 2, '.', ''));
        $el->setAttribute('currencyID', $currency);
        $parent->appendChild($el);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Convierte un número a letras en español (para Note SUNAT).
     */
    public static function numeroALetras(float $numero, string $moneda = 'SOLES'): string
    {
        $entero = (int) floor($numero);
        $decimales = (int) round(($numero - $entero) * 100);
        $letrasEntero = self::convertirEntero($entero);
        $letras = strtoupper("SON {$letrasEntero} CON {$decimales}/100 {$moneda}");
        return preg_replace('/\s+/', ' ', $letras);
    }

    private static function convertirEntero(int $n): string
    {
        if ($n === 0) return 'CERO';
        if ($n < 0)  return 'MENOS ' . self::convertirEntero(-$n);

        $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
                     'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE',
                     'DIECIOCHO', 'DIECINUEVE', 'VEINTE'];
        $decenas  = ['', '', 'VEINTI', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
                     'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($n <= 20) return $unidades[$n];
        if ($n < 100) {
            $d = (int) ($n / 10);
            $u = $n % 10;
            if ($d === 2 && $u > 0) return $decenas[$d] . strtolower($unidades[$u]);
            return $decenas[$d] . ($u > 0 ? ' Y ' . $unidades[$u] : '');
        }
        if ($n === 100) return 'CIEN';
        if ($n < 1000) {
            $c = (int) ($n / 100);
            $r = $n % 100;
            return $centenas[$c] . ($r > 0 ? ' ' . self::convertirEntero($r) : '');
        }
        if ($n < 1000000) {
            $m = (int) ($n / 1000);
            $r = $n % 1000;
            $miles = ($m === 1) ? 'MIL' : self::convertirEntero($m) . ' MIL';
            return $miles . ($r > 0 ? ' ' . self::convertirEntero($r) : '');
        }
        $mill = (int) ($n / 1000000);
        $r = $n % 1000000;
        $millones = ($mill === 1) ? 'UN MILLÓN' : self::convertirEntero($mill) . ' MILLONES';
        return $millones . ($r > 0 ? ' ' . self::convertirEntero($r) : '');
    }
}
