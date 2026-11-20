
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multas', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 40)->unique();
            $table->string('ci', 20)->index();
            $table->string('nombre_conductor', 150);

            $table->string('tipo_infraccion', 150);
            $table->text('descripcion')->nullable();
            $table->string('lugar', 200);

            $table->dateTime('fecha_infraccion');
            $table->decimal('monto', 10, 2);

            $table->enum('estado', [
                'pendiente',
                'pagada',
                'anulada'
            ])->default('pendiente');

            $table->string('policia_id', 50)->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multas');
    }
};