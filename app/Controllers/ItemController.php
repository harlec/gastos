<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Item;
use InvalidArgumentException;

class ItemController extends Controller
{
    public function store(string $planId): void
    {
        $data = $this->input();
        $categoria = (string) ($data['categoria'] ?? '');
        $dia = isset($data['dia']) && $data['dia'] !== '' && $data['dia'] !== null ? (int) $data['dia'] : null;

        try {
            $item = Item::create(
                (int) $planId,
                $categoria,
                trim((string) ($data['nombre'] ?? '')),
                $dia,
                max(0, (float) ($data['monto'] ?? 0)),
                (bool) ($data['es_necesario'] ?? false)
            );
        } catch (InvalidArgumentException $e) {
            $this->json(['error' => $e->getMessage()], 422);
            return;
        }

        $this->json($item, 201);
    }

    public function update(string $id): void
    {
        Item::update((int) $id, $this->input());
        $this->json(['ok' => true]);
    }

    public function destroy(string $id): void
    {
        Item::delete((int) $id);
        $this->json(['ok' => true]);
    }
}
