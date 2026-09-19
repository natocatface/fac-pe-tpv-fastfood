<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique()->nullable();
            $table->string('nombre', 100);
            $table->string('apellidos', 150)->nullable();
            $table->string('nif_cif', 30)->nullable();
            $table->string('email', 150)->nullable()->index();
            $table->string('telefono', 30)->nullable()->index();
            $table->string('movil', 30)->nullable()->index();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('genero', ['M', 'F', 'Otro'])->nullable();
            // Dirección principal
            $table->string('direccion', 250)->nullable();
            $table->string('numero', 20)->nullable();
            $table->string('piso', 20)->nullable();
            $table->string('puerta', 10)->nullable();
            $table->string('codigo_postal', 15)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('pais', 80)->default('España');
            $table->text('referencia_direccion')->nullable()->comment('Indicaciones para el repartidor');
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            // CRM
            $table->enum('tipo', ['particular', 'empresa', 'vip'])->default('particular');
            $table->text('notas')->nullable();
            $table->json('preferencias')->nullable()->comment('Alergias, productos favoritos, etc.');
            $table->integer('puntos_fidelidad')->default(0);
            $table->decimal('saldo_credito', 10, 2)->default(0);
            $table->decimal('descuento_fijo', 5, 2)->default(0)->comment('% de descuento permanente');
            $table->integer('total_pedidos')->default(0);
            $table->decimal('total_gastado', 12, 2)->default(0);
            $table->timestamp('ultimo_pedido_at')->nullable();
            $table->boolean('acepta_marketing')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['nombre', 'apellidos']);
        });

        Schema::create('cliente_direcciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('alias', 50)->default('Dirección');
            $table->string('direccion', 250);
            $table->string('numero', 20)->nullable();
            $table->string('piso', 20)->nullable();
            $table->string('puerta', 10)->nullable();
            $table->string('codigo_postal', 15)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->text('referencia')->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->boolean('predeterminada')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_direcciones');
        Schema::dropIfExists('clientes');
    }
};
