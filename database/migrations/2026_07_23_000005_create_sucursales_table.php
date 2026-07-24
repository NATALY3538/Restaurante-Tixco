<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('direccion_calle', 255);
            $table->string('colonia_ciudad', 150);
            $table->string('codigo_postal', 20);
            $table->string('telefono_contacto', 30);
            $table->string('email_contacto', 120);
            $table->string('rfc_identificacion_fiscal', 30)->nullable();
            $table->string('horario_apertura', 10)->default('08:00');
            $table->string('horario_cierre', 10)->default('23:00');
            $table->json('dias_operacion')->nullable();
            $table->boolean('is_matriz')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
