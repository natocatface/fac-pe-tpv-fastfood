<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 120)->unique();
            $table->text('descripcion')->nullable();
            $table->string('icono', 50)->nullable()->comment('FontAwesome icon class');
            $table->string('color', 7)->default('#28a745');
            $table->string('imagen', 250)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activa')->default(true);
            $table->boolean('mostrar_tpv')->default(true);
            $table->timestamps();

            $table->index(['activa', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
