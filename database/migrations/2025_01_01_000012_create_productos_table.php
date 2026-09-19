<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('restrict');
            $table->string('codigo', 50)->unique()->nullable();
            $table->string('codigo_barras', 50)->nullable()->index();
            $table->string('nombre', 150);
            $table->string('slug', 180)->unique();
            $table->text('descripcion')->nullable();
            $table->text('ingredientes')->nullable();
            $table->text('alergenos')->nullable();
            $table->decimal('precio', 10, 2);
            $table->decimal('precio_costo', 10, 2)->default(0);
            $table->decimal('precio_oferta', 10, 2)->nullable();
            $table->date('oferta_inicio')->nullable();
            $table->date('oferta_fin')->nullable();
            $table->decimal('iva', 5, 2)->default(10.00);
            $table->string('imagen', 250)->nullable();
            $table->json('imagenes_extra')->nullable();
            $table->enum('tipo', ['simple', 'menu', 'combo', 'compuesto'])->default('simple');
            $table->boolean('controla_stock')->default(false);
            $table->decimal('stock', 10, 2)->default(0);
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->string('unidad_medida', 20)->default('unidad');
            $table->integer('tiempo_preparacion')->default(0)->comment('en minutos');
            $table->integer('calorias')->nullable();
            $table->boolean('es_vegetariano')->default(false);
            $table->boolean('es_vegano')->default(false);
            $table->boolean('es_sin_gluten')->default(false);
            $table->boolean('picante')->default(false);
            $table->integer('nivel_picante')->default(0)->comment('0-5');
            $table->boolean('disponible_local')->default(true);
            $table->boolean('disponible_domicilio')->default(true);
            $table->boolean('disponible_recogida')->default(true);
            $table->boolean('destacado')->default(false);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->integer('vendidos')->default(0);
            $table->timestamps();

            $table->index(['categoria_id', 'activo']);
            $table->index('destacado');
        });

        Schema::create('producto_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->string('nombre', 100);
            $table->decimal('precio_extra', 8, 2)->default(0);
            $table->boolean('obligatorio')->default(false);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('producto_tamanos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->string('nombre', 50)->comment('Pequeña, Mediana, Grande, Familiar');
            $table->decimal('precio', 10, 2);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_tamanos');
        Schema::dropIfExists('producto_extras');
        Schema::dropIfExists('productos');
    }
};
