
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('multa_id')
                ->unique()
                ->constrained('multas')
                ->cascadeOnDelete();

            $table->string('entidad', 100);
            $table->decimal('monto', 10, 2);
            $table->string('referencia', 100)->unique();
            $table->dateTime('fecha_pago');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};