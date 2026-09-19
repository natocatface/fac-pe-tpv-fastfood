<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            // Datos empresa
            $table->string('nombre_empresa', 150)->default('Mi Negocio FastFood');
            $table->string('razon_social', 200)->nullable();
            $table->string('cif_nif', 30)->nullable();
            $table->string('direccion', 250)->nullable();
            $table->string('codigo_postal', 15)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('pais', 80)->default('España');
            $table->string('telefono', 30)->nullable();
            $table->string('movil', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('web', 150)->nullable();
            $table->string('logo', 250)->nullable();
            $table->string('favicon', 250)->nullable();

            // Moneda e impuestos
            $table->string('moneda_codigo', 5)->default('EUR');
            $table->string('moneda_simbolo', 5)->default('€');
            $table->enum('moneda_posicion', ['izquierda', 'derecha'])->default('derecha');
            $table->integer('decimales')->default(2);
            $table->string('separador_decimal', 1)->default(',');
            $table->string('separador_miles', 1)->default('.');
            $table->decimal('iva_general', 5, 2)->default(10.00);
            $table->decimal('iva_reducido', 5, 2)->default(4.00);
            $table->boolean('precios_con_iva')->default(true);

            // TPV
            $table->string('serie_ticket', 10)->default('T');
            $table->integer('proximo_ticket')->default(1);
            $table->string('serie_factura', 10)->default('F');
            $table->integer('proximo_factura')->default(1);
            $table->string('serie_pedido', 10)->default('P');
            $table->integer('proximo_pedido')->default(1);
            $table->text('texto_ticket_cabecera')->nullable();
            $table->text('texto_ticket_pie')->nullable();
            $table->boolean('imprimir_logo_ticket')->default(true);
            $table->integer('ancho_ticket_mm')->default(80);

            // Operativa negocio
            $table->boolean('modulo_domicilio')->default(true);
            $table->boolean('modulo_mesas')->default(true);
            $table->boolean('modulo_recogida')->default(true);
            $table->boolean('modulo_telefono')->default(true);
            $table->decimal('coste_envio', 8, 2)->default(0);
            $table->decimal('pedido_minimo_envio', 8, 2)->default(0);
            $table->time('hora_apertura')->default('09:00');
            $table->time('hora_cierre')->default('23:30');

            // Apariencia
            $table->string('color_primario', 7)->default('#28a745');
            $table->string('color_secundario', 7)->default('#343a40');
            $table->enum('tema', ['claro', 'oscuro', 'auto'])->default('claro');

            // Notificaciones
            $table->boolean('email_pedidos')->default(false);
            $table->string('email_notificaciones', 150)->nullable();
            $table->boolean('alerta_stock_bajo')->default(true);
            $table->integer('umbral_stock_bajo')->default(5);

            // Fidelización
            $table->boolean('programa_puntos')->default(false);
            $table->decimal('puntos_por_euro', 6, 2)->default(1);
            $table->decimal('valor_punto', 6, 4)->default(0.01);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
