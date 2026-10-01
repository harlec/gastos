<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use InvalidArgumentException;

class Item extends Model
{
    public const CATEGORIAS = ['fijo', 'compra', 'semanal', 'diario', 'deuda'];

    public static function forPlan(int $planId): array
    {
        $stmt = self::db()->prepare('SELECT * FROM items WHERE plan_id = ? ORDER BY id');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public static function create(int $planId, string $categoria, string $nombre, ?int $dia, float $monto, bool $esNecesario): array
    {
        self::assertCategoria($categoria);

        $stmt = self::db()->prepare(
            'INSERT INTO items (plan_id, categoria, nombre, dia, monto, es_necesario) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$planId, $categoria, $nombre, $dia, $monto, $esNecesario ? 1 : 0]);
        $id = (int) self::db()->lastInsertId();

        return [
            'id' => $id,
            'plan_id' => $planId,
            'categoria' => $categoria,
            'nombre' => $nombre,
            'dia' => $dia,
            'monto' => $monto,
            'es_necesario' => $esNecesario,
        ];
    }

    public static function update(int $id, array $fields): void
    {
        $allowed = ['nombre', 'dia', 'monto', 'es_necesario'];
        $sets = [];
        $params = [];

        foreach ($fields as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue;
            }
            if ($key === 'es_necesario') {
                $value = $value ? 1 : 0;
            }
            if ($key === 'dia' && $value !== null) {
                $value = (int) $value;
            }
            if ($key === 'monto') {
                $value = (float) $value;
            }
            $sets[] = "$key = ?";
            $params[] = $value;
        }

        if (!$sets) {
            return;
        }

        $params[] = $id;
        $stmt = self::db()->prepare('UPDATE items SET ' . implode(', ', $sets) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $stmt = self::db()->prepare('DELETE FROM items WHERE id = ?');
        $stmt->execute([$id]);
    }

    private static function assertCategoria(string $categoria): void
    {
        if (!in_array($categoria, self::CATEGORIAS, true)) {
            throw new InvalidArgumentException('Categoría inválida: ' . $categoria);
        }
    }
}
