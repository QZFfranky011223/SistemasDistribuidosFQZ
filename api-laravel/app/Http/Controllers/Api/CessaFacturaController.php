<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CessaFacturaController extends Controller
{
    // Archivo de persistencia rápida para no depender de migraciones complejas
    private static function getStorageFile()
    {
        return storage_path('cessa_facturas.json');
    }

    private static function loadFacturas()
    {
        $file = self::getStorageFile();
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true) ?? [];
        }

        // Datos iniciales si no existe el archivo
        $inicial = [
            ['id' => 1, 'nroFactura' => 'C-101', 'ci' => '1234567', 'empresa' => 'CESSA', 'periodo' => '2026-01', 'monto' => 150.50, 'pagado' => false],
            ['id' => 2, 'nroFactura' => 'C-102', 'ci' => '1234567', 'empresa' => 'CESSA', 'periodo' => '2026-02', 'monto' => 180.00, 'pagado' => false],
            ['id' => 3, 'nroFactura' => 'C-103', 'ci' => '7654321', 'empresa' => 'CESSA', 'periodo' => '2026-01', 'monto' => 95.00, 'pagado' => false],
        ];
        file_put_contents($file, json_encode($inicial, JSON_PRETTY_PRINT));
        return $inicial;
    }

    private static function saveFacturas($facturas)
    {
        file_put_contents(self::getStorageFile(), json_encode($facturas, JSON_PRETTY_PRINT));
    }

    // GET /api/cessa/facturas?ci=1234567
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

    // POST /api/cessa/facturas/pagar
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
                'mensaje' => 'Factura de CESSA pagada exitosamente en el sistema CESSA',
                'factura' => $facturaPagada
            ]);
        }

        return response()->json([
            'success' => false,
            'mensaje' => 'Factura no encontrada o ya pagada'
        ], 404);
    }
}
