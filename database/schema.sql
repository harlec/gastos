-- Esquema para MariaDB. Ejecutar una sola vez sobre la base de datos vacía
-- que crees en Plesk (Bases de datos > Añadir base de datos).

CREATE TABLE IF NOT EXISTS planes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL DEFAULT 'Mi plan del mes',
  anio SMALLINT UNSIGNED NOT NULL,
  mes TINYINT UNSIGNED NOT NULL,
  ingreso_principal DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_planes_periodo (anio, mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ingresos_extra (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  plan_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL DEFAULT '',
  monto DECIMAL(10,2) NOT NULL DEFAULT 0,
  CONSTRAINT fk_ingresos_extra_plan FOREIGN KEY (plan_id) REFERENCES planes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  plan_id INT UNSIGNED NOT NULL,
  categoria ENUM('fijo','compra','semanal','diario','deuda') NOT NULL,
  nombre VARCHAR(120) NOT NULL DEFAULT '',
  dia TINYINT UNSIGNED NULL,
  monto DECIMAL(10,2) NOT NULL DEFAULT 0,
  es_necesario TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_items_plan FOREIGN KEY (plan_id) REFERENCES planes(id) ON DELETE CASCADE,
  INDEX idx_items_plan_categoria (plan_id, categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
