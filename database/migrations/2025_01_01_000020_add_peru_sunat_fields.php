<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============ Configuración: extiende con campos SUNAT/Perú ============
        Schema::table('configuracion', function (Blueprint $table) {
            $table->boolean('facturacion_electronica_pe')->default(false)->after('valor_punto')->comment('Activa el módulo de facturación electrónica Perú');
            $table->string('ruc', 11)->nullable()->after('facturacion_electronica_pe');
            $table->string('ubigeo', 6)->nullable()->after('ruc')->comment('Código de ubigeo INEI');
            $table->string('urbanizacion', 100)->nullable()->after('ubigeo');
            $table->string('distrito', 80)->nullable()->after('urbanizacion');
            $table->string('departamento', 80)->nullable()->after('distrito');
            $table->string('usuario_sol', 50)->nullable()->after('departamento');
            $table->string('clave_sol', 100)->nullable()->after('usuario_sol');
            $table->enum('sunat_modo', ['beta', 'produccion'])->default('beta')->after('clave_sol');
            $table->string('certificado_path', 250)->nullable()->after('sunat_modo')->comment('Ruta al .pem');
            $table->string('certificado_password', 100)->nullable()->after('certificado_path');
            $table->string('serie_factura_pe', 10)->default('F001')->after('certificado_password');
            $table->string('serie_boleta_pe', 10)->default('B001')->after('serie_factura_pe');
            $table->integer('proximo_factura_pe')->default(1)->after('serie_boleta_pe');
            $table->integer('proximo_boleta_pe')->default(1)->after('proximo_factura_pe');
            $table->decimal('igv_porcentaje', 5, 2)->default(18.00)->after('proximo_boleta_pe');
        });

        // ============ Clientes: tipo de documento de identidad SUNAT ============
        Schema::table('clientes', function (Blueprint $table) {
            $table->enum('tipo_documento_pe', ['0', '1', '4', '6', '7', 'A', 'B', 'C', 'D', 'E'])
                ->nullable()->after('nif_cif')
                ->comment('Catálogo 06 SUNAT: 1=DNI, 6=RUC, 7=Pasaporte, 4=CE, 0=Sin documento');
            $table->string('razon_social', 250)->nullable()->after('apellidos');
        });

        // ============ Tabla principal de comprobantes electrónicos ============
        Schema::create('comprobantes_electronicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->onDelete('set null');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Identificación del comprobante (Catálogo 01 SUNAT)
            $table->enum('tipo', ['01', '03', '07', '08'])->comment('01=Factura, 03=Boleta, 07=NC, 08=ND');
            $table->string('serie', 10);
            $table->integer('numero');
            $table->string('numero_completo', 30)->unique()->comment('SERIE-NUMERO ej F001-00000001');

            $table->date('fecha_emision');
            $table->time('hora_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();

            // Receptor
            $table->string('tipo_doc_receptor', 2)->comment('1=DNI, 6=RUC, 7=Pasaporte, 4=CE, 0=Sin doc');
            $table->string('num_doc_receptor', 20);
            $table->string('razon_social_receptor', 250);
            $table->string('direccion_receptor', 300)->nullable();
            $table->string('email_receptor', 150)->nullable();

            // Moneda e importes
            $table->string('moneda', 3)->default('PEN');
            $table->decimal('total_gravado',    14, 2)->default(0)->comment('Operaciones gravadas');
            $table->decimal('total_exonerado',  14, 2)->default(0);
            $table->decimal('total_inafecto',   14, 2)->default(0);
            $table->decimal('total_exportacion',14, 2)->default(0);
            $table->decimal('total_gratuito',   14, 2)->default(0);
            $table->decimal('total_descuento',  14, 2)->default(0);
            $table->decimal('igv',              14, 2)->default(0);
            $table->decimal('total',            14, 2)->default(0);
            $table->string('total_letras', 300)->nullable();

            // Documento que modifica (para NC/ND)
            $table->string('doc_modificado_tipo', 2)->nullable();
            $table->string('doc_modificado_numero', 30)->nullable();
            $table->string('motivo_anulacion', 250)->nullable();
            $table->string('codigo_motivo_nota', 5)->nullable()->comment('Catálogo 09 (NC) / 10 (ND)');

            // Estado del flujo electrónico
            $table->enum('estado', [
                'borrador',          // Recién creado
                'generado',          // XML generado
                'firmado',           // XML firmado digitalmente
                'enviado',           // Enviado a SUNAT (esperando respuesta)
                'aceptado',          // Aceptado por SUNAT (CDR OK)
                'observado',         // Aceptado con observaciones
                'rechazado',         // Rechazado por SUNAT
                'anulado',           // Comunicado de baja realizado
                'error',             // Error de comunicación
            ])->default('borrador');

            // Archivos
            $table->string('xml_path', 300)->nullable();
            $table->string('xml_firmado_path', 300)->nullable();
            $table->string('cdr_path', 300)->nullable();
            $table->string('pdf_path', 300)->nullable();
            $table->string('hash_xml', 100)->nullable()->comment('DigestValue de la firma');

            // Respuesta SUNAT
            $table->string('codigo_sunat', 10)->nullable();
            $table->string('mensaje_sunat', 500)->nullable();
            $table->json('observaciones')->nullable();
            $table->integer('intentos_envio')->default(0);
            $table->timestamp('enviado_at')->nullable();
            $table->timestamp('aceptado_at')->nullable();

            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'estado']);
            $table->index('fecha_emision');
        });

        // ============ Líneas de comprobante (detalle) ============
        Schema::create('comprobante_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobantes_electronicos')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onDelete('set null');

            $table->integer('orden');
            $table->string('codigo_producto', 50)->nullable();
            $table->string('descripcion', 500);
            $table->string('unidad_medida', 10)->default('NIU')->comment('NIU=Unidad, KGM=Kilogramo');
            $table->decimal('cantidad', 12, 4);
            $table->decimal('valor_unitario', 14, 4)->comment('Sin IGV');
            $table->decimal('precio_unitario', 14, 4)->comment('Con IGV');
            $table->decimal('descuento', 14, 2)->default(0);
            $table->string('tipo_afectacion_igv', 2)->default('10')->comment('Cat. 07: 10=Gravado, 20=Exonerado, 30=Inafecto');
            $table->decimal('igv', 14, 2)->default(0);
            $table->decimal('valor_total', 14, 2)->comment('Sin IGV');
            $table->decimal('total', 14, 2)->comment('Con IGV');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobante_lineas');
        Schema::dropIfExists('comprobantes_electronicos');
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['tipo_documento_pe', 'razon_social']);
        });
        Schema::table('configuracion', function (Blueprint $table) {
            $table->dropColumn([
                'facturacion_electronica_pe', 'ruc', 'ubigeo', 'urbanizacion', 'distrito',
                'departamento', 'usuario_sol', 'clave_sol', 'sunat_modo',
                'certificado_path', 'certificado_password',
                'serie_factura_pe', 'serie_boleta_pe', 'proximo_factura_pe', 'proximo_boleta_pe',
                'igv_porcentaje',
            ]);
        });
    }
};
