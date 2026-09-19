<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('color', 7)->default('#17a2b8');
            $table->integer('orden')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zona_id')->nullable()->constrained('zonas')->onDelete('set null');
            $table->string('numero', 20);
            $table->string('nombre', 80)->nullable();
            $table->integer('capacidad')->default(4);
            $table->enum('estado', ['libre', 'ocupada', 'reservada', 'cobrando', 'cerrada'])->default('libre');
            $table->integer('pos_x')->default(0)->comment('Posición en plano');
            $table->integer('pos_y')->default(0);
            $table->string('forma', 20)->default('cuadrada');
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['zona_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mesas');
        Schema::dropIfExists('zonas');
    }
};
