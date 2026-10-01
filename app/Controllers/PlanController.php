<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\IngresoExtra;
use App\Models\Item;
use App\Models\Plan;

class PlanController extends Controller
{
    public function index(): void
    {
        $plan = Plan::latest();

        if (!$plan) {
            $id = Plan::create('Mi plan del mes', (int) date('Y'), (int) date('n'), 2600);
            Item::create($id, 'fijo', 'Alquiler', 5, 0, true);
            Item::create($id, 'fijo', 'Colegio', 10, 0, true);
            $plan = Plan::find($id);
        }

        header('Location: /plan/' . $plan['id']);
    }

    public function show(string $id): void
    {
        $plan = Plan::find((int) $id);

        if (!$plan) {
            http_response_code(404);
            $this->render('plan/not-found');
            return;
        }

        $this->render('plan/show', [
            'plan' => $plan,
            'planes' => Plan::all(),
            'ingresosExtra' => IngresoExtra::forPlan((int) $id),
            'items' => Item::forPlan((int) $id),
        ]);
    }

    private const MESES_ES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
        7 => 'Julio', 8 => 'Agosto', 9 => 'Setiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public function store(): void
    {
        $data = $_POST ?: $this->input();

        $anio = (int) ($data['anio'] ?? date('Y'));
        $mes = max(1, min(12, (int) ($data['mes'] ?? date('n'))));
        $ingreso = max(0, (float) ($data['ingreso_principal'] ?? 0));

        $nombre = trim((string) ($data['nombre'] ?? ''));
        if ($nombre === '') {
            $nombre = (self::MESES_ES[$mes] ?? $mes) . ' ' . $anio;
        }

        $id = Plan::create($nombre, $anio, $mes, $ingreso);
        Item::create($id, 'fijo', 'Alquiler', 5, 0, true);
        Item::create($id, 'fijo', 'Colegio', 10, 0, true);

        header('Location: /plan/' . $id);
    }

    public function updateIngreso(string $id): void
    {
        $data = $this->input();
        Plan::updateIngresoPrincipal((int) $id, max(0, (float) ($data['ingreso_principal'] ?? 0)));
        $this->json(['ok' => true]);
    }

    public function rename(string $id): void
    {
        $data = $this->input();
        Plan::rename((int) $id, trim((string) ($data['nombre'] ?? '')));
        $this->json(['ok' => true]);
    }

    public function destroy(string $id): void
    {
        Plan::delete((int) $id);
        $this->json(['ok' => true]);
    }
}
