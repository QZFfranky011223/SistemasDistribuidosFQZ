<?php
namespace App\Http\Controllers;
use App\Models\Medicamento;
use App\Models\Laboratorio;

class DashboardController extends Controller {
    public function __invoke() {
        $totalMedicamentos = Medicamento::count();
        $stockBajo = Medicamento::where('stock', '<', 10)->count();
        $porLaboratorio = Laboratorio::withCount('medicamentos')->get();
        return view('dashboard', compact('totalMedicamentos', 'stockBajo', 'porLaboratorio'));
    }
}
