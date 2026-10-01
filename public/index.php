<?php
declare(strict_types=1);

use App\Core\Router;

define('BASE_PATH', dirname(__DIR__));

function gastos_error_page(string $title, string $detail): void
{
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><title>' . htmlspecialchars($title) . '</title></head>';
    echo '<body style="font-family:-apple-system,sans-serif;max-width:720px;margin:60px auto;line-height:1.6;color:#222;padding:0 20px">';
    echo '<h1 style="color:#b91c1c;font-size:20px">' . htmlspecialchars($title) . '</h1>';
    echo '<pre style="white-space:pre-wrap;word-break:break-word;background:#f6f6f6;border:1px solid #ddd;border-radius:8px;padding:14px;font-size:13px">' . htmlspecialchars($detail) . '</pre>';
    echo '</body></html>';
}

// Si algo revienta de forma fatal (versión de PHP incompatible, error de sintaxis
// en un archivo incluido, etc.) esto evita que la página quede en blanco sin pista
// alguna: siempre vas a ver el motivo en pantalla.
register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        gastos_error_page(
            'Error fatal de PHP',
            $error['message'] . "\n\n" . $error['file'] . ':' . $error['line']
        );
    }
});

try {
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
        gastos_error_page(
            'Falta configurar la base de datos',
            "Copia app/Config/config.example.php a app/Config/config.php\n"
            . "y coloca ahí los datos reales de tu base de datos MariaDB\n"
            . "(host, nombre, usuario y contraseña)."
        );
        exit;
    }
    require $configPath;

    $router = new Router();
    require BASE_PATH . '/app/routes.php';

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $router->dispatch($_SERVER['REQUEST_METHOD'], $path);
} catch (\Throwable $e) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    gastos_error_page(
        get_class($e) . ': ' . $e->getMessage(),
        $e->getFile() . ':' . $e->getLine() . "\n\n" . $e->getTraceAsString()
    );
}
