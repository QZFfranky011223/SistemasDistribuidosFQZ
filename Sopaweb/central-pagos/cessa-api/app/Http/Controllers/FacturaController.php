<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    // GET /api/facturas?ci=1234567 (Obtiene las deudas no pagadas)
    public function index(Request $request)
    {
        $query = Factura::query();

        if ($request->has('ci')) {
            $query->where('ci', $request->ci)
                  ->where('pagado', false);
        }

        return response()->json($query->get(), 200);
    }

    // GET /api/facturas/{id}
    public function show(string $id)
    {
        $factura = Factura::find($id);
        if (!$factura) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }
        return response()->json($factura, 200);
    }

    // PUT /api/facturas/{id} (Registra el pago de la deuda)
    public function update(Request $request, string $id)
    {
        $factura = Factura::find($id);
        if (!$factura) {
            return response()->json(['error' => 'Factura no encontrada'], 404);
        }

        $factura->pagado = true;
        $factura->save();

        return response()->json([
            'mensaje' => 'Pago realizado con exito en CESSA',
            'factura' => $factura
        ], 200);
    }
}