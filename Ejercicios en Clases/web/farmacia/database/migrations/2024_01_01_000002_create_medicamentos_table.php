<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial', 80);
            $table->string('principio_activo');
            $table->enum('presentacion', ['Tabletas', 'Jarabe', 'Inyectable', 'Cápsulas']);
            $table->decimal('precio', 10, 2);
            $table->unsignedInteger('stock');
            $table->boolean('requiere_receta')->default(false);
            $table->foreignId('laboratorio_id')->constrained('laboratorios')->onDelete('cascade');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('medicamentos'); }
};
