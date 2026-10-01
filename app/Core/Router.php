<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<int, array{method:string, regex:string, handler:array, params:string[]}> */
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function patch(string $path, array $handler): void
    {
        $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        preg_match_all('#\{([a-zA-Z_]+)\}#', $path, $matches);
        $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $path);

        $this->routes[] = [
            'method' => $method,
            'regex' => '#^' . $pattern . '$#',
            'handler' => $handler,
            'params' => $matches[1],
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = rtrim($uri, '/');
        if ($path === '') {
            $path = '/';
        }

        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['regex'], $path, $matches)) {
                array_shift($matches);
                $args = array_combine($route['params'], $matches);
                [$controllerClass, $action] = $route['handler'];
                $controller = new $controllerClass();
                $controller->$action(...array_values($args));
                return;
            }
        }

        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Ruta no encontrada']);
    }
}
