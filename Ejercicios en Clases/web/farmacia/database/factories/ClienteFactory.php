<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory {
    public function definition(): array {
        return [
            'nombre_completo' => $this->faker->name(),
            'ci' => $this->faker->unique()->numerify('########'),
            'email' => $this->faker->unique()->safeEmail(),
            'telefono' => $this->faker->phoneNumber(),
            'tipo_cliente' => $this->faker->randomElement(['Regular', 'Frecuente', 'Asegurado']),
        ];
    }
}
