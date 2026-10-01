<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\IngresoExtra;

class IngresoExtraController extends Controller
{
    public function store(string $planId): void
    {
        $data = $this->input();

        $ingreso = IngresoExtra::create(
            (int) $planId,
            trim((string) ($data['nombre'] ?? '')),
            max(0, (float) ($data['monto'] ?? 0))
        );

        $this->json($ingreso, 201);
    }

    public function update(string $id): void
    {
        IngresoExtra::update((int) $id, $this->input());
        $this->json(['ok' => true]);
    }

    public function destroy(string $id): void
    {
        IngresoExtra::delete((int) $id);
        $this->json(['ok' => true]);
    }
}
