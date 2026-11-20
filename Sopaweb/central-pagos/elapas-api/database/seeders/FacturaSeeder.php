<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Factura;

class FacturaSeeder extends Seeder
{
    public function run(): void
    {
        // Facturas de agua potable de ELAPAS
        Factura::create(['ci' => '1234567', 'periodo' => '2024-09', 'monto' => 65.50, 'pagado' => false]);
        Factura::create(['ci' => '1234567', 'periodo' => '2024-10', 'monto' => 70.00, 'pagado' => false]);
        Factura::create(['ci' => '7654321', 'periodo' => '2024-10', 'monto' => 50.00, 'pagado' => false]);
    }
}