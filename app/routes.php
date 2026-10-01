<?php
declare(strict_types=1);

use App\Controllers\HistorialController;
use App\Controllers\IngresoExtraController;
use App\Controllers\ItemController;
use App\Controllers\PlanController;

/** @var \App\Core\Router $router */

$router->get('/', [PlanController::class, 'index']);
$router->get('/historial', [HistorialController::class, 'index']);
$router->get('/plan/{id}', [PlanController::class, 'show']);
$router->post('/plan', [PlanController::class, 'store']);

$router->patch('/api/planes/{id}/ingreso-principal', [PlanController::class, 'updateIngreso']);
$router->patch('/api/planes/{id}/nombre', [PlanController::class, 'rename']);
$router->delete('/api/planes/{id}', [PlanController::class, 'destroy']);

$router->post('/api/planes/{planId}/ingresos-extra', [IngresoExtraController::class, 'store']);
$router->patch('/api/ingresos-extra/{id}', [IngresoExtraController::class, 'update']);
$router->delete('/api/ingresos-extra/{id}', [IngresoExtraController::class, 'destroy']);

$router->post('/api/planes/{planId}/items', [ItemController::class, 'store']);
$router->patch('/api/items/{id}', [ItemController::class, 'update']);
$router->delete('/api/items/{id}', [ItemController::class, 'destroy']);
