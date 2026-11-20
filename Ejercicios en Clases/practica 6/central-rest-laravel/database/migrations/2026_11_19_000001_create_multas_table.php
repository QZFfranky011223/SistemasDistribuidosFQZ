<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('multas')) {
            Schema::create('multas', function (Blueprint $table) {
                $table->id();
                $table->string('codigo_unico')->unique();
                $table->string('ci_conductor');
                $table->string('nombre_conductor');
                $table->string('placa')->default('S/N');
                $table->string('fecha');
                $table->string('tipo_infraccion');
                $table->string('lugar');
                $table->decimal('monto', 10, 2);
                $table->string('estado')->default('pendiente');
                $table->string('fecha_pago')->nullable();
                $table->string('entidad_pago')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('multas');
    }
};
