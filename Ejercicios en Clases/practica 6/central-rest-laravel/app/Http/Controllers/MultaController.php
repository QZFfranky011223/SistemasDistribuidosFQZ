<?php

namespace App\Http\Controllers;

use App\Models\Multa;
use Illuminate\Http\Request;

class MultaController extends Controller
{
    /**
     * GET /api/multa
     * Lista todas las multas o filtra por ?ci= o ?codigo_unico= o ?estado=
     */
    public function index(Request $request)
    {
        $query = Multa::query()->orderBy('id', 'desc');

        if ($request->filled('ci')) {
            $query->whereRaw('LOWER(ci_conductor) = LOWER(?)', [trim($request->query('ci'))]);
        }
        if ($request->filled('ci_conductor')) {
            $query->whereRaw('LOWER(ci_conductor) = LOWER(?)', [trim($request->query('ci_conductor'))]);
        }
        if ($request->filled('codigo_unico')) {
            $query->whereRaw('UPPER(codigo_unico) = UPPER(?)', [trim($request->query('codigo_unico'))]);
        }
        if ($request->filled('estado')) {
            $query->whereRaw('LOWER(estado) = LOWER(?)', [trim($request->query('estado'))]);
        }

        $multas = $query->get();

        return response()->json([
            'exito' => true,
            'total' => $multas->count(),
            'data' => $multas,
        ]);
    }

    /**
     * POST /api/multa
     * Registra una nueva boleta de infracción emitida por el Policía de Tránsito (Forms C#)
     * tras haber validado el CI del conductor con el servicio SOAP del SEGIP.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_unico'     => 'required|string|max:50|unique:multas,codigo_unico',
            'ci_conductor'     => 'required|string|max:30',
            'nombre_conductor' => 'required|string|max:150',
            'placa'            => 'nullable|string|max:30',
            'fecha'            => 'required|string|max:30',
            'tipo_infraccion'  => 'required|string|max:200',
            'lugar'            => 'required|string|max:200',
            'monto'            => 'required|numeric|min:1',
        ]);

        $multa = Multa::create([
            'codigo_unico'     => strtoupper(trim($validated['codigo_unico'])),
            'ci_conductor'     => trim($validated['ci_conductor']),
            'nombre_conductor' => trim($validated['nombre_conductor']),
            'placa'            => !empty($validated['placa']) ? strtoupper(trim($validated['placa'])) : 'S/N',
            'fecha'            => trim($validated['fecha']),
            'tipo_infraccion'  => trim($validated['tipo_infraccion']),
            'lugar'            => trim($validated['lugar']),
            'monto'            => (float) $validated['monto'],
            'estado'           => 'pendiente',
        ]);

        return response()->json([
            'exito'   => true,
            'mensaje' => 'Boleta de infracción registrada correctamente en la Central de Registro.',
            'data'    => $multa,
        ], 201);
    }

    /**
     * GET /api/multa/{idOrCodigo}
     * Permite al Cliente Web consultar una boleta por su código único (ej. BOL-1001) o su ID.
     */
    public function show(string $idOrCodigo)
    {
        $multa = Multa::whereRaw('UPPER(codigo_unico) = UPPER(?)', [trim($idOrCodigo)])
            ->orWhere('id', $idOrCodigo)
            ->first();

        if (!$multa) {
            return response()->json([
                'exito'   => false,
                'mensaje' => "La boleta '{$idOrCodigo}' no existe en la Central de Registro.",
            ], 404);
        }

        return response()->json([
            'exito' => true,
            'data'  => $multa,
        ]);
    }

    /**
     * PUT /api/multa/{idOrCodigo}/pagar  (o PUT /api/multa/{idOrCodigo})
     * Utilizado por el Servidor de Sockets TCP en Java cuando la Entidad Recaudadora
     * registra el pago de la boleta, cambiando su estado de "pendiente" a "pagada".
     */
    public function pagar(Request $request, string $idOrCodigo)
    {
        $multa = Multa::whereRaw('UPPER(codigo_unico) = UPPER(?)', [trim($idOrCodigo)])
            ->orWhere('id', $idOrCodigo)
            ->first();

        if (!$multa) {
            return response()->json([
                'exito'   => false,
                'mensaje' => "La boleta '{$idOrCodigo}' no existe en el sistema.",
            ], 404);
        }

        // Si el socket envía ci_conductor, verificar que la boleta haya sido emitida para ese conductor
        $ciEnviado = trim((string) $request->input('ci_conductor', ''));
        if ($ciEnviado !== '' && strtolower($ciEnviado) !== strtolower($multa->ci_conductor)) {
            return response()->json([
                'exito'   => false,
                'mensaje' => "La boleta '{$multa->codigo_unico}' no pertenece al conductor con CI '{$ciEnviado}' (emitida para CI {$multa->ci_conductor}).",
            ], 400);
        }

        $estadoActual = strtolower($multa->estado);
        if ($estadoActual === 'pagada') {
            return response()->json([
                'exito'   => false,
                'mensaje' => "La boleta '{$multa->codigo_unico}' ya fue pagada previamente en fecha {$multa->fecha_pago}.",
            ], 400);
        }

        if ($estadoActual === 'anulada') {
            return response()->json([
                'exito'   => false,
                'mensaje' => "La boleta '{$multa->codigo_unico}' está anulada y no puede ser pagada.",
            ], 400);
        }

        $multa->estado = 'pagada';
        $multa->fecha_pago = now()->format('Y-m-d H:i:s');
        $multa->entidad_pago = $request->input('entidad_pago', 'Entidad Recaudadora Autorizada');
        $multa->save();

        return response()->json([
            'exito'      => true,
            'mensaje'    => "Pago registrado exitosamente. Estado de boleta '{$multa->codigo_unico}' actualizado a 'pagada'.",
            'monto'      => $multa->monto,
            'fecha_pago' => $multa->fecha_pago,
            'data'       => $multa,
        ]);
    }
}
