# 🇵🇪 Facturación Electrónica Perú (SUNAT) - Guía de uso

Módulo completo de facturación electrónica con envío **directo a SUNAT** (sin intermediarios OSE) implementado en este CRM.

## ✨ Características

- **Emisión electrónica** de Facturas (01) y Boletas (03) en formato **UBL 2.1**
- **Firma digital XMLDSig** sobre los XML con certificado .pem/.pfx
- **Envío SOAP directo** al billService de SUNAT (Beta y Producción)
- Parseo automático del **CDR** (Constancia de Recepción) devuelto por SUNAT
- Numeración correlativa automática (series F001/B001)
- Catálogos SUNAT vigentes (01 tipo doc, 06 tipo identidad, 07 tipo afectación IGV)
- Total en letras automático en español ("SON CIENTO VEINTICUATRO CON 50/100 SOLES")
- Conversión IGV inverso desde precios con IGV (igual al funcionamiento del TPV)
- Almacenamiento de XML, XML firmado y CDR ZIP
- UI completa con badges de estado, filtros y descarga de archivos
- Integración con el TPV: emite comprobante a partir de cualquier pedido

## 🚀 Activación rápida

### 1. Migrar la base de datos

```bash
php artisan migrate
```

Esto crea las tablas `comprobantes_electronicos` y `comprobante_lineas`, y añade los campos SUNAT al `configuracion` y `clientes`.

### 2. Activar el módulo

Entra a **Configuración → Pestaña "SUNAT (Perú)"** y rellena:

#### Activación
- ✅ **Activar facturación electrónica Perú**
- **Modo:** `🧪 Beta` para pruebas / `🚀 Producción` cuando estés listo

#### Datos del emisor
- **RUC** (11 dígitos)
- **Ubigeo** (6 dígitos INEI - p.ej. `150101` para Lima)
- **Departamento, Distrito, Urbanización**

#### Credenciales SOL
- **Usuario SOL** (en Beta: `MODDATOS`)
- **Clave SOL** (en Beta: `moddatos`)

#### Certificado digital
- Sube el archivo `.pem` o `.pfx` proporcionado por una entidad acreditada
- Para pruebas Beta puedes generar uno gratuito desde [SUNAT Personalización](https://www.sunat.gob.pe/legislacion/superintendencia/2017/anexos/anexoVII-318-2017.pdf)
- Introduce la **contraseña** del certificado

#### Numeración y tributos
- **Serie Factura**: F001 (por defecto)
- **Serie Boleta**: B001 (por defecto)
- **IGV %**: 18.00 (vigente)

### 3. Probar en Beta

Una vez configurado, ve a **Pedidos → cualquier pedido cobrado → "Emitir comprobante SUNAT"**.

Elige tipo (Factura/Boleta), introduce documento del receptor, y pulsa **"Emitir y enviar a SUNAT"**. El sistema:

1. Genera el XML UBL 2.1
2. Lo firma digitalmente
3. Lo empaqueta en ZIP
4. Lo envía al endpoint Beta de SUNAT
5. Recibe el CDR y muestra el resultado

## 📁 Estructura de archivos

```
app/
├── Models/
│   ├── ComprobanteElectronico.php       # Modelo principal
│   └── ComprobanteLinea.php             # Líneas del comprobante
├── Services/Sunat/
│   ├── UblXmlBuilder.php                # Genera XML UBL 2.1
│   ├── FirmaDigitalService.php          # Firma XMLDSig
│   ├── SunatSoapClient.php              # Cliente SOAP billService
│   └── FacturacionElectronicaService.php # Orquestador
├── Http/Controllers/
│   └── ComprobanteElectronicoController.php

database/migrations/
└── 2025_01_01_000020_add_peru_sunat_fields.php

resources/views/comprobantes/
├── index.blade.php                       # Listado con stats
├── show.blade.php                        # Detalle con archivos
└── emitir.blade.php                      # Formulario emisión
```

## 🔄 Flujo del documento

```
┌─────────────┐  emitir  ┌──────────┐  procesar  ┌──────────┐
│   PEDIDO    │ ───────▶ │ BORRADOR │ ────────▶  │ GENERADO │
│  (cobrado)  │          │          │            │   (XML)  │
└─────────────┘          └──────────┘            └────┬─────┘
                                                      │
                                       ┌──────────────▼──────────────┐
                                       │  FIRMADO  →  ENVIADO        │
                                       │  (XMLDSig)   (SOAP a SUNAT) │
                                       └──────────────┬──────────────┘
                                                      │
                              ┌───────────────────────┴───────────────────────┐
                              │                                               │
                       ┌──────▼──────┐                                ┌──────▼──────┐
                       │  ACEPTADO   │                                │  RECHAZADO  │
                       │ (código 0)  │                                │ (código 2x) │
                       └─────────────┘                                └─────────────┘
```

## 📂 Catálogos SUNAT implementados

| Catálogo | Uso | Valores |
|----------|-----|---------|
| **01** | Tipo de comprobante | `01`=Factura, `03`=Boleta, `07`=NC, `08`=ND |
| **06** | Tipo doc. identidad | `1`=DNI, `4`=CE, `6`=RUC, `7`=Pasaporte |
| **07** | Tipo afectación IGV | `10`=Gravado, `20`=Exonerado, `30`=Inafecto |
| **09** | Motivo Nota Crédito | `01`=Anulación, `07`=Devolución, etc |
| **10** | Motivo Nota Débito | `01`=Intereses, `02`=Aumento de valor |
| **51** | Tipo de operación | `0101`=Venta interna |
| **52** | Códigos de leyendas | `1000`=Total en letras |

## 🔐 Endpoints SUNAT

```
Beta (homologación):
  https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService

Producción:
  https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService
```

## 📜 Ejemplo de XML generado (fragmento)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
         xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
         xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
  <ext:UBLExtensions>
    <ext:UBLExtension>
      <ext:ExtensionContent>
        <ds:Signature ...><!-- firma XMLDSig --></ds:Signature>
      </ext:ExtensionContent>
    </ext:UBLExtension>
  </ext:UBLExtensions>
  <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
  <cbc:CustomizationID>2.0</cbc:CustomizationID>
  <cbc:ID>F001-00000001</cbc:ID>
  <cbc:IssueDate>2026-05-18</cbc:IssueDate>
  <cbc:InvoiceTypeCode listID="0101">01</cbc:InvoiceTypeCode>
  <cbc:Note languageLocaleID="1000"><![CDATA[SON CIENTO VEINTICUATRO CON 50/100 SOLES]]></cbc:Note>
  <cbc:DocumentCurrencyCode>PEN</cbc:DocumentCurrencyCode>
  ...
</Invoice>
```

## 🧪 Probar firma sin enviar a SUNAT

Si quieres validar que el XML se genera correctamente sin envío real, ve a la pantalla del comprobante (estado borrador o generado) y descarga el XML para revisarlo manualmente.

## 🐛 Errores comunes y soluciones

| Error SUNAT | Causa | Solución |
|---|---|---|
| **0150** | Mensaje firma inválido | Revisa que el certificado coincida con el RUC del emisor |
| **0156** | RUC del emisor diferente | El RUC en `configuracion` debe coincidir con el del certificado |
| **2335** | Período tributario inválido | La fecha de emisión es muy antigua o futura |
| **3105** | Sumas no cuadran | Hay un desfase en redondeos. Revisa importes de las líneas |
| **CertificadoNoEncontrado** | No subido o no accesible | Vuelve a subir el .pem desde Configuración |

## ⚙️ Para producción

1. Cambia el modo a `🚀 Producción` en Configuración
2. Sustituye el certificado de prueba por uno **acreditado por INDECOPI**
3. Usa tu **Usuario SOL y Clave SOL real** (los obtienes en SUNAT Operaciones en Línea)
4. Reinicia la numeración con tu serie real autorizada por SUNAT

## 📞 Soporte SUNAT

- Mesa de Servicios SUNAT: `0-801-12-100`
- Documentación técnica: `https://cpe.sunat.gob.pe/`
- Especificación UBL: `https://docs.oasis-open.org/ubl/UBL-2.1.html`
