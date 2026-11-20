<?php
namespace App\Http\Controllers;
use App\Models\Medicamento;
use App\Models\Laboratorio;
use App\Http\Requests\MedicamentoRequest;
use Illuminate\Http\Request;

class MedicamentoController extends Controller {
    public function index(Request $request) {
        $query = Medicamento::with('laboratorio');
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where('nombre_comercial', 'like', "%$q%")
                  ->orWhere('principio_activo', 'like', "%$q%");
        }
        $medicamentos = $query->paginate(10);
        return view('medicamentos.index', compact('medicamentos'));
    }
    public function create() {
        $laboratorios = Laboratorio::all();
        return view('medicamentos.create', compact('laboratorios'));
    }
    public function store(MedicamentoRequest $request) {
        Medicamento::create($request->validated());
        return redirect()->route('medicamentos.index')->with('success', 'Medicamento creado exitosamente.');
    }
    public function show(Medicamento $medicamento) {
        return view('medicamentos.show', compact('medicamento'));
    }
    public function edit(Medicamento $medicamento) {
        $laboratorios = Laboratorio::all();
        return view('medicamentos.edit', compact('medicamento', 'laboratorios'));
    }
    public function update(MedicamentoRequest $request, Medicamento $medicamento) {
        $medicamento->update($request->validated());
        return redirect()->route('medicamentos.index')->with('success', 'Medicamento actualizado.');
    }
    public function destroy(Medicamento $medicamento) {
        $medicamento->delete();
        return redirect()->route('medicamentos.index')->with('success', 'Medicamento eliminado.');
    }
}
