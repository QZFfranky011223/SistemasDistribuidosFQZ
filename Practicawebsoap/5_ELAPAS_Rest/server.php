<?php
// Servidor REST ELAPAS - Recurso Facturas
// Puerto por defecto: 8002

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$dataFile = __DIR__ . '/elapas_facturas.json';
if (!file_exists($dataFile)) {
    $inicial = [
        ['id' => 1, 'nroFactura' => 'E-201', 'ci' => '1234567', 'empresa' => 'ELAPAS', 'periodo' => '2026-01', 'monto' => 85.00, 'pagado' => false],
        ['id' => 2, 'nroFactura' => 'E-202', 'ci' => '1234567', 'empresa' => 'ELAPAS', 'periodo' => '2026-02', 'monto' => 92.50, 'pagado' => false],
        ['id' => 3, 'nroFactura' => 'E-203', 'ci' => '8888888', 'empresa' => 'ELAPAS', 'periodo' => '2026-03', 'monto' => 110.00, 'pagado' => false],
    ];
    file_put_contents($dataFile, json_encode($inicial, JSON_PRETTY_PRINT));
}

$facturas = json_decode(file_get_contents($dataFile), true) ?? [];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// GET /facturas o /api/elapas/facturas
if ($method === 'GET' && (strpos($uri, 'facturas') !== false)) {
    $ci = $_GET['ci'] ?? null;
    if ($ci) {
        $filtradas = array_values(array_filter($facturas, function($f) use ($ci) {
            return $f['ci'] == $ci && !$f['pagado'];
        }));
        echo json_encode($filtradas);
    } else {
        echo json_encode($facturas);
    }
    exit();
}

// POST /facturas/pagar o /api/elapas/facturas/pagar
if ($method === 'POST' && (strpos($uri, 'pagar') !== false)) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    $nroFactura = $input['nroFactura'] ?? null;

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
        file_put_contents($dataFile, json_encode($facturas, JSON_PRETTY_PRINT));
        echo json_encode([
            'success' => true,
            'mensaje' => 'Factura ELAPAS pagada exitosamente en el sistema ELAPAS',
            'factura' => $facturaPagada
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'mensaje' => 'Factura no encontrada o ya pagada'
        ]);
    }
    exit();
}

echo json_encode(['mensaje' => 'Servicio REST ELAPAS activo']);
