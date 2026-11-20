<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaboratorioFactory extends Factory {
    public function definition(): array {
        return [
            'nombre' => $this->faker->company(),
            'pais' => $this->faker->country(),
            'telefono' => $this->faker->phoneNumber(),
        ];
    }
}
