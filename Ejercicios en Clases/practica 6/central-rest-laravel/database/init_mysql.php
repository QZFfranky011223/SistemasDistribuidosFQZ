<?php
/**
 * Inicializador de la Base de Datos MySQL: bd_multas_transito
 * Detecta automáticamente el puerto MySQL de XAMPP (3307 o 3306), usuario root sin password.
 */

$host = getenv('DB_HOST') ?: '127.0.0.1';
$dbName = getenv('DB_DATABASE') ?: 'bd_multas_transito';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

$portsToTry = getenv('DB_PORT') ? [getenv('DB_PORT')] : ['3307', '3306'];
$pdo = null;
$activePort = '3307';
$lastError = '';

foreach ($portsToTry as $port) {
    try {
        $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 3,
        ]);
        $activePort = $port;
        break;
    } catch (Throwable $e) {
        $lastError = $e->getMessage();
    }
}

if (!$pdo) {
    fwrite(STDERR, "[Aviso MySQL] No se pudo conectar a MySQL ({$lastError}).\n");
    exit(1);
}

try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `{$dbName}`;");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `multas` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `codigo_unico` VARCHAR(50) NOT NULL,
            `ci_conductor` VARCHAR(30) NOT NULL,
            `nombre_conductor` VARCHAR(150) NOT NULL,
            `placa` VARCHAR(30) NOT NULL DEFAULT 'S/N',
            `fecha` VARCHAR(30) NOT NULL,
            `tipo_infraccion` VARCHAR(200) NOT NULL,
            `lugar` VARCHAR(200) NOT NULL,
            `monto` DECIMAL(10, 2) NOT NULL,
            `estado` VARCHAR(30) NOT NULL DEFAULT 'pendiente',
            `fecha_pago` VARCHAR(30) NULL DEFAULT NULL,
            `entidad_pago` VARCHAR(150) NULL DEFAULT NULL,
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `multas_codigo_unico_unique` (`codigo_unico`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $count = (int) $pdo->query("SELECT COUNT(*) FROM `multas`")->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare("
            INSERT INTO `multas` (`codigo_unico`, `ci_conductor`, `nombre_conductor`, `placa`, `fecha`, `tipo_infraccion`, `lugar`, `monto`, `estado`, `fecha_pago`, `entidad_pago`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute(['BOL-1001', '1234567', 'Juan Pérez Mamani', '4521-TKR', '2026-11-19', 'Exceso de velocidad en zona urbana', 'Av. Mariscal Santa Cruz esq. Colón', 250.00, 'pendiente', null, null]);
        $stmt->execute(['BOL-1002', '7654321', 'María Quispe Condori', '3019-ABC', '2026-11-19', 'Cruzar semáforo en luz roja', 'Av. 6 de Agosto esq. Campos', 350.00, 'pendiente', null, null]);
        $stmt->execute(['BOL-1003', '8888888', 'Carlos Flores Vargas', '1920-XYZ', '2026-11-18', 'Estacionamiento en lugar prohibido', 'Calle Comercio Nº 450', 150.00, 'pagada', '2026-11-18 15:30:00', 'Banco Unión - Caja 1']);
    }

    echo "[MySQL OK] Base de datos '{$dbName}' y tabla 'multas' listas en {$host}:{$activePort} (Total boletas: " . $pdo->query("SELECT COUNT(*) FROM `multas`")->fetchColumn() . ").\n";
} catch (Throwable $e) {
    fwrite(STDERR, "[Error MySQL] {$e->getMessage()}\n");
    exit(1);
}
