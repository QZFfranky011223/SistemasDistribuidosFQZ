<?php
namespace Database\Factories;
use App\Models\Laboratorio;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicamentoFactory extends Factory {
    public function definition(): array {
        return [
            'nombre_comercial' => $this->faker->word() . ' ' . $this->faker->numberBetween(100,500) . 'mg',
            'principio_activo' => $this->faker->word(),
            'presentacion' => $this->faker->randomElement(['Tabletas', 'Jarabe', 'Inyectable', 'Cápsulas']),
            'precio' => $this->faker->randomFloat(2, 5, 500),
            'stock' => $this->faker->numberBetween(0, 200),
            'requiere_receta' => $this->faker->boolean(),
            'laboratorio_id' => Laboratorio::inRandomOrder()->first()?->id ?? Laboratorio::factory(),
        ];
    }
}
