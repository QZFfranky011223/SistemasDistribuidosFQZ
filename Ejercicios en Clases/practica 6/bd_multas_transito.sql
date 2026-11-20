-- ============================================================================
-- Base de Datos MySQL: bd_multas_transito
-- Sistema Distribuido de Gestión de Multas de Tránsito en Línea (Práctica 6)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `bd_multas_transito`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `bd_multas_transito`;

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

-- Datos iniciales de prueba
INSERT IGNORE INTO `multas` (`codigo_unico`, `ci_conductor`, `nombre_conductor`, `placa`, `fecha`, `tipo_infraccion`, `lugar`, `monto`, `estado`, `fecha_pago`, `entidad_pago`)
VALUES
  ('BOL-1001', '1234567', 'Juan Pérez Mamani', '4521-TKR', '2026-11-19', 'Exceso de velocidad en zona urbana', 'Av. Mariscal Santa Cruz esq. Colón', 250.00, 'pendiente', NULL, NULL),
  ('BOL-1002', '7654321', 'María Quispe Condori', '3019-ABC', '2026-11-19', 'Cruzar semáforo en luz roja', 'Av. 6 de Agosto esq. Campos', 350.00, 'pendiente', NULL, NULL),
  ('BOL-1003', '8888888', 'Carlos Flores Vargas', '1920-XYZ', '2026-11-18', 'Estacionamiento en lugar prohibido', 'Calle Comercio Nº 450', 150.00, 'pagada', '2026-11-18 15:30:00', 'Banco Unión - Caja 1');
