<?php
/**
 * Consulta directa a la base de datos MySQL `bd_multas_transito` para el Servidor GraphQL (Node.js)
 * Soporta automáticamente el puerto MySQL 3307 (XAMPP) o 3306.
 */

$action = $argv[1] ?? 'all';
$param = $argv[2] ?? '';

$host = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_DATABASE') ?: 'bd_multas_transito';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

$portsToTry = getenv('DB_PORT') ? [getenv('DB_PORT')] : ['3307', '3306'];
$pdo = null;
$lastErr = '';

foreach ($portsToTry as $port) {
    try {
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        break;
    } catch (Throwable $e) {
        $lastErr = $e->getMessage();
    }
}

if (!$pdo) {
    echo json_encode(['ok' => false, 'error' => $lastErr], JSON_UNESCAPED_UNICODE);
    exit(1);
}

try {
    if ($action === 'by_codigo') {
        $stmt = $pdo->prepare("SELECT * FROM `multas` WHERE UPPER(`codigo_unico`) = UPPER(?) LIMIT 1");
        $stmt->execute([$param]);
        $row = $stmt->fetch();
        echo json_encode(['ok' => true, 'engine' => 'mysql', 'row' => $row ?: null], JSON_UNESCAPED_UNICODE);
    } elseif ($action === 'by_ci') {
        $stmt = $pdo->prepare("SELECT * FROM `multas` WHERE LOWER(`ci_conductor`) = LOWER(?) ORDER BY `id` DESC");
        $stmt->execute([$param]);
        echo json_encode(['ok' => true, 'engine' => 'mysql', 'rows' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
    } elseif ($action === 'by_estado') {
        $stmt = $pdo->prepare("SELECT * FROM `multas` WHERE LOWER(`estado`) = LOWER(?) ORDER BY `id` DESC");
        $stmt->execute([$param]);
        echo json_encode(['ok' => true, 'engine' => 'mysql', 'rows' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
    } else {
        $stmt = $pdo->query("SELECT * FROM `multas` ORDER BY `id` DESC");
        echo json_encode(['ok' => true, 'engine' => 'mysql', 'rows' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit(1);
}
