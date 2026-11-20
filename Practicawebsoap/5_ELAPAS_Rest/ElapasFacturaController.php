<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Controlador de ELAPAS para Laravel
 * Ruta GET:  /api/elapas/facturas?ci={ci}
 * Ruta POST: /api/elapas/facturas/pagar
 */
class ElapasFacturaController extends Controller
{
    private static function getStorageFile()
    {
        return storage_path('elapas_facturas.json');
    }

    private static function loadFacturas()
    {
        $file = self::getStorageFile();
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true) ?? [];
        }

        $inicial = [
            ['id' => 1, 'nroFactura' => 'E-201', 'ci' => '1234567', 'empresa' => 'ELAPAS', 'periodo' => '2026-01', 'monto' => 85.00, 'pagado' => false],
            ['id' => 2, 'nroFactura' => 'E-202', 'ci' => '1234567', 'empresa' => 'ELAPAS', 'periodo' => '2026-02', 'monto' => 92.50, 'pagado' => false],
            ['id' => 3, 'nroFactura' => 'E-203', 'ci' => '8888888', 'empresa' => 'ELAPAS', 'periodo' => '2026-03', 'monto' => 110.00, 'pagado' => false],
        ];
        file_put_contents($file, json_encode($inicial, JSON_PRETTY_PRINT));
        return $inicial;
    }

    private static function saveFacturas($facturas)
    {
        file_put_contents(self::getStorageFile(), json_encode($facturas, JSON_PRETTY_PRINT));
    }

    public function index(Request $request): JsonResponse
    {
        $ci = $request->query('ci');
        $facturas = self::loadFacturas();

        if ($ci) {
            $filtradas = array_values(array_filter($facturas, function($f) use ($ci) {
                return $f['ci'] == $ci && !$f['pagado'];
            }));
            return response()->json($filtradas);
        }

        return response()->json($facturas);
    }

    public function pagar(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $nroFactura = $request->input('nroFactura');

        $facturas = self::loadFacturas();
        $encontrada = false;
        $facturaPagada = null;

        foreach ($facturas as &$f) {
            if (($id && $f['id'] == $id) || ($nroFactura && $f['nroFactura'] == $nroFactura)) {
                $f['pagado'] = true;
                $f['fechaPago'] = date('Y-m-d H:i:s');
                $encontrada = true;
                $facturaPagada = $f;
                break;
            }
        }

        if ($encontrada) {
            self::saveFacturas($facturas);
            return response()->json([
                'success' => true,
                'mensaje' => 'Factura ELAPAS pagada exitosamente en el sistema ELAPAS',
                'factura' => $facturaPagada
            ]);
        }

        return response()->json([
            'success' => false,
            'mensaje' => 'Factura no encontrada o ya pagada'
        ], 404);
    }
}
