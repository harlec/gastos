<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Plan;

class HistorialController extends Controller
{
    public function index(): void
    {
        $porAnio = Plan::agrupadosPorAnio();

        $anios = [];
        foreach ($porAnio as $anio => $planes) {
            $filas = [];
            $totales = ['ingreso_total' => 0.0, 'gasto_total' => 0.0];

            foreach ($planes as $plan) {
                $resumen = Plan::resumen($plan);
                $filas[] = ['plan' => $plan, 'resumen' => $resumen];
                $totales['ingreso_total'] += $resumen['ingreso_total'];
                $totales['gasto_total'] += $resumen['gasto_total'];
            }

            $anios[] = ['anio' => $anio, 'filas' => $filas, 'totales' => $totales];
        }

        $this->render('historial/index', ['anios' => $anios]);
    }
}
