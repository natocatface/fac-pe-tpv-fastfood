<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->datetime('fecha_apertura');
            $table->datetime('fecha_cierre')->nullable();
            $table->decimal('saldo_inicial', 12, 2)->default(0);
            $table->decimal('total_efectivo', 12, 2)->default(0);
            $table->decimal('total_tarjeta', 12, 2)->default(0);
            $table->decimal('total_transferencia', 12, 2)->default(0);
            $table->decimal('total_otros', 12, 2)->default(0);
            $table->decimal('total_ventas', 12, 2)->default(0);
            $table->decimal('total_gastos', 12, 2)->default(0);
            $table->decimal('saldo_final_calculado', 12, 2)->default(0);
            $table->decimal('saldo_final_real', 12, 2)->nullable();
            $table->decimal('descuadre', 12, 2)->default(0);
            $table->integer('num_pedidos')->default(0);
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->timestamps();
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->nullable()->constrained('cajas')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('mesa_id')->nullable()->constrained('mesas')->onDelete('set null');
            $table->string('numero', 30)->unique();
            $table->string('serie', 10)->default('P');
            $table->enum('tipo', ['mesa', 'mostrador', 'domicilio', 'recogida', 'telefono'])->default('mostrador');
            $table->enum('estado', [
                'borrador', 'pendiente', 'en_preparacion', 'preparado',
                'en_camino', 'entregado', 'cobrado', 'anulado'
            ])->default('borrador');
            $table->datetime('fecha_pedido');
            $table->datetime('fecha_entrega')->nullable();
            $table->datetime('fecha_cobro')->nullable();
            // Datos cliente snapshot
            $table->string('cliente_nombre', 200)->nullable();
            $table->string('cliente_telefono', 30)->nullable();
            $table->string('cliente_direccion', 300)->nullable();
            $table->text('cliente_referencia')->nullable();
            // Importes
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('coste_envio', 10, 2)->default(0);
            $table->decimal('total_iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('pagado', 12, 2)->default(0);
            $table->decimal('cambio', 10, 2)->default(0);
            // Otros
            $table->text('notas')->nullable();
            $table->text('notas_cocina')->nullable();
            $table->integer('num_comensales')->nullable();
            $table->boolean('factura_emitida')->default(false);
            $table->string('numero_factura', 30)->nullable();
            $table->timestamps();

            $table->index(['estado', 'fecha_pedido']);
            $table->index(['tipo', 'estado']);
            $table->index('fecha_pedido');
        });

        Schema::create('pedido_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onDelete('set null');
            $table->string('nombre_producto', 200);
            $table->string('tamano', 50)->nullable();
            $table->decimal('cantidad', 8, 2)->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento_pct', 5, 2)->default(0);
            $table->decimal('descuento_importe', 10, 2)->default(0);
            $table->decimal('iva_pct', 5, 2)->default(10);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->json('extras')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado_cocina', ['pendiente', 'preparando', 'listo', 'entregado'])->default('pendiente');
            $table->timestamps();
        });

        Schema::create('pedido_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('caja_id')->nullable()->constrained('cajas')->onDelete('set null');
            $table->enum('metodo', ['efectivo', 'tarjeta', 'transferencia', 'bizum', 'vale', 'puntos', 'otro'])->default('efectivo');
            $table->decimal('importe', 12, 2);
            $table->string('referencia', 100)->nullable();
            $table->datetime('fecha');
            $table->timestamps();
        });

        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('tipo', ['ingreso', 'gasto', 'apertura', 'cierre', 'ajuste'])->default('gasto');
            $table->string('concepto', 200);
            $table->decimal('importe', 12, 2);
            $table->string('metodo', 30)->default('efectivo');
            $table->datetime('fecha');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
        Schema::dropIfExists('pedido_pagos');
        Schema::dropIfExists('pedido_detalles');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('cajas');
    }
};
