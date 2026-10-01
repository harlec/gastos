<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

session_start();

$configPath = BASE_PATH . '/app/Config/config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Configuración pendiente</title></head><body style="font-family:sans-serif;max-width:640px;margin:60px auto;line-height:1.5">';
    echo '<h1>Falta configurar la base de datos</h1>';
    echo '<p>Copia <code>app/Config/config.example.php</code> a <code>app/Config/config.php</code> y coloca ahí los datos de tu base de datos MariaDB (host, nombre, usuario y contraseña).</p>';
    echo '</body></html>';
    exit;
}
require $configPath;

use App\Core\Router;

$router = new Router();
require BASE_PATH . '/app/routes.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$router->dispatch($_SERVER['REQUEST_METHOD'], $path);
