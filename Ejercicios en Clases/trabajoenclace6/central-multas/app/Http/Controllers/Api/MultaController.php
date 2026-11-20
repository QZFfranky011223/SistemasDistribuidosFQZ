<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Multa;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Throwable;

class MultaController extends Controller
{
    // LISTAR Y BUSCAR MULTAS
    public function index(Request $request)
    {
        $query = Multa::with('pago');

        if ($request->filled('ci')) {
            $query->where('ci', $request->ci);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('codigo')) {
            $query->where('codigo', $request->codigo);
        }

        $multas = $query->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'mensaje' => 'Multas consultadas correctamente',
            'data' => $multas
        ], 200);
    }

    // REGISTRAR UNA MULTA
    public function store(Request $request)
    {
        $datos = $request->validate([
            'ci' => 'required|string|max:20',
            'nombre_conductor' => 'required|string|max:150',
            'tipo_infraccion' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'lugar' => 'required|string|max:200',
            'fecha_infraccion' => 'required|date',
            'monto' => 'required|numeric|min:0.01',
            'policia_id' => 'nullable|string|max:50',
        ]);

        $datos['codigo'] = 'MULTA-' .
            now()->format('Ymd') . '-' .
            strtoupper(Str::random(8));

        $datos['estado'] = 'pendiente';

        $multa = Multa::create($datos);

        return response()->json([
            'success' => true,
            'mensaje' => 'Multa registrada correctamente',
            'data' => $multa
        ], 201);
    }

    // CONSULTAR UNA MULTA POR SU CODIGO
    public function show(string $codigo)
    {
        $multa = Multa::with('pago')
            ->where('codigo', $codigo)
            ->first();

        if (!$multa) {
            return response()->json([
                'success' => false,
                'mensaje' => 'La multa no existe'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $multa
        ], 200);
    }

    // REGISTRAR EL PAGO DE UNA MULTA
    public function pagar(Request $request, string $codigo)
    {
        $datos = $request->validate([
            'ci' => 'required|string|max:20',
            'entidad' => 'required|string|max:100',
            'referencia' => 'required|string|max:100',
        ]);

        try {
            $resultado = DB::transaction(function () use (
                $codigo,
                $datos
            ) {
                $multa = Multa::where('codigo', $codigo)
                    ->lockForUpdate()
                    ->first();

                if (!$multa) {
                    return [
                        'error' => 'MULTA_NO_EXISTE',
                        'mensaje' => 'La multa no existe'
                    ];
                }

                if ($multa->ci !== $datos['ci']) {
                    return [
                        'error' => 'CI_NO_COINCIDE',
                        'mensaje' => 'El CI no corresponde al titular'
                    ];
                }

                if ($multa->estado === 'anulada') {
                    return [
                        'error' => 'MULTA_ANULADA',
                        'mensaje' => 'La multa está anulada'
                    ];
                }

                if ($multa->estado === 'pagada') {
                    return [
                        'error' => 'MULTA_YA_PAGADA',
                        'mensaje' => 'La multa ya fue pagada'
                    ];
                }

                if (Pago::where('referencia', $datos['referencia'])
                    ->exists()) {
                    return [
                        'error' => 'REFERENCIA_DUPLICADA',
                        'mensaje' => 'La referencia ya fue utilizada'
                    ];
                }

                $pago = Pago::create([
                    'multa_id' => $multa->id,
                    'entidad' => $datos['entidad'],
                    'monto' => $multa->monto,
                    'referencia' => $datos['referencia'],
                    'fecha_pago' => now(),
                ]);

                $multa->update([
                    'estado' => 'pagada'
                ]);

                return [
                    'pago' => $pago,
                    'multa' => $multa->fresh()
                ];
            }, attempts: 3);

            if (isset($resultado['error'])) {
                $codigos = [
                    'MULTA_NO_EXISTE' => 404,
                    'CI_NO_COINCIDE' => 403,
                    'MULTA_ANULADA' => 409,
                    'MULTA_YA_PAGADA' => 409,
                    'REFERENCIA_DUPLICADA' => 409,
                ];

                return response()->json([
                    'success' => false,
                    'codigo_error' => $resultado['error'],
                    'mensaje' => $resultado['mensaje']
                ], $codigos[$resultado['error']]);
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Pago registrado correctamente',
                'data' => $resultado
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'mensaje' => 'No se pudo registrar el pago. Verifique la referencia.'
            ], 409);

        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'mensaje' => 'Error interno al procesar el pago'
            ], 500);
        }
    }

    // ANULAR UNA MULTA
    public function anular(string $codigo)
    {
        $multa = Multa::where('codigo', $codigo)->first();

        if (!$multa) {
            return response()->json([
                'success' => false,
                'mensaje' => 'Multa no encontrada'
            ], 404);
        }

        if ($multa->estado === 'pagada') {
            return response()->json([
                'success' => false,
                'mensaje' => 'No se puede anular una multa pagada'
            ], 409);
        }

        if ($multa->estado === 'anulada') {
            return response()->json([
                'success' => false,
                'mensaje' => 'La multa ya está anulada'
            ], 409);
        }

        $multa->update([
            'estado' => 'anulada'
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Multa anulada correctamente',
            'data' => $multa
        ], 200);
    }
}