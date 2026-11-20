<?php
namespace Database\Seeders;
use App\Models\Laboratorio;
use App\Models\Medicamento;
use App\Models\Cliente;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        Laboratorio::factory(5)->create();
        Medicamento::factory(20)->create();
        Cliente::factory(30)->create();
    }
}
