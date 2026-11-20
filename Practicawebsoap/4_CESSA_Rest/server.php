<?php
// Servidor REST CESSA - Recurso Facturas
// Puerto por defecto: 8001

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$dataFile = __DIR__ . '/cessa_facturas.json';
if (!file_exists($dataFile)) {
    $inicial = [
        ['id' => 1, 'nroFactura' => 'C-101', 'ci' => '1234567', 'empresa' => 'CESSA', 'periodo' => '2026-01', 'monto' => 150.50, 'pagado' => false],
        ['id' => 2, 'nroFactura' => 'C-102', 'ci' => '1234567', 'empresa' => 'CESSA', 'periodo' => '2026-02', 'monto' => 180.00, 'pagado' => false],
        ['id' => 3, 'nroFactura' => 'C-103', 'ci' => '7654321', 'empresa' => 'CESSA', 'periodo' => '2026-01', 'monto' => 95.00, 'pagado' => false],
    ];
    file_put_contents($dataFile, json_encode($inicial, JSON_PRETTY_PRINT));
}

$facturas = json_decode(file_get_contents($dataFile), true) ?? [];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// GET /facturas o /api/cessa/facturas
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

// POST /facturas/pagar o /api/cessa/facturas/pagar
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
            'mensaje' => 'Factura CESSA pagada exitosamente en el sistema CESSA',
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

echo json_encode(['mensaje' => 'Servicio REST CESSA activo']);
