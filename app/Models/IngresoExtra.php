<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class IngresoExtra extends Model
{
    public static function forPlan(int $planId): array
    {
        $stmt = self::db()->prepare('SELECT * FROM ingresos_extra WHERE plan_id = ? ORDER BY id');
        $stmt->execute([$planId]);
        return $stmt->fetchAll();
    }

    public static function create(int $planId, string $nombre, float $monto): array
    {
        $stmt = self::db()->prepare(
            'INSERT INTO ingresos_extra (plan_id, nombre, monto) VALUES (?, ?, ?)'
        );
        $stmt->execute([$planId, $nombre, $monto]);
        $id = (int) self::db()->lastInsertId();

        return ['id' => $id, 'plan_id' => $planId, 'nombre' => $nombre, 'monto' => $monto];
    }

    public static function update(int $id, array $fields): void
    {
        $allowed = ['nombre', 'monto'];
        $sets = [];
        $params = [];

        foreach ($fields as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue;
            }
            $sets[] = "$key = ?";
            $params[] = $key === 'monto' ? (float) $value : (string) $value;
        }

        if (!$sets) {
            return;
        }

        $params[] = $id;
        $stmt = self::db()->prepare('UPDATE ingresos_extra SET ' . implode(', ', $sets) . ' WHERE id = ?');
        $stmt->execute($params);
    }

    public static function delete(int $id): void
    {
        $stmt = self::db()->prepare('DELETE FROM ingresos_extra WHERE id = ?');
        $stmt->execute([$id]);
    }
}
