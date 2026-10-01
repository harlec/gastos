<?php
declare(strict_types=1);

// Copia este archivo como "config.php" en la misma carpeta y coloca aquí
// los datos reales de la base de datos MariaDB que crees en Plesk.

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'gastos');
define('DB_USER', 'gastos_user');
define('DB_PASS', 'cambia-esta-clave');

// 'local' muestra errores en pantalla, útil mientras desarrollas.
// En el subdominio del VPS déjalo en 'production'.
define('APP_ENV', 'production');
define('APP_DEBUG', APP_ENV === 'local');

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}
