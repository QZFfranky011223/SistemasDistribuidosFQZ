<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$filePath = dirname(__DIR__) . '/storage/app/live_stream.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode((string) $raw, true);

    if (is_array($input)) {
        $input['updated_at'] = time();
        $input['updated_time'] = date('H:i:s');
        file_put_contents($filePath, json_encode($input));
        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['ok' => false]);
    exit;
}

if (! file_exists($filePath)) {
    echo json_encode(['is_live' => false]);
    exit;
}

$data = json_decode((string) file_get_contents($filePath), true);
if (! is_array($data)) {
    echo json_encode(['is_live' => false]);
    exit;
}

if (time() - ($data['updated_at'] ?? 0) > 60) {
    $data['is_live'] = false;
}

echo json_encode($data);
