<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Plan extends Model
{
    public static function all(): array
    {
        $stmt = self::db()->query('SELECT * FROM planes ORDER BY anio DESC, mes DESC, id DESC');
        return $stmt->fetchAll();
    }

    /** Todos los planes agrupados por año, para la vista de historial. */
    public static function agrupadosPorAnio(): array
    {
        $porAnio = [];
        foreach (self::all() as $plan) {
            $porAnio[(int) $plan['anio']][] = $plan;
        }
        krsort($porAnio);
        foreach ($porAnio as &$planes) {
            usort($planes, static fn ($a, $b) => (int) $a['mes'] <=> (int) $b['mes']);
        }
        return $porAnio;
    }

    /**
     * Calcula el resumen (ingresos, gastos, disponible por día, etc.) de un
     * plan a partir de sus ingresos extra e ítems, con la misma fórmula que
     * usa el recálculo en el navegador (assets/js/app.js).
     */
    public static function resumen(array $plan): array
    {
        $principal = (float) $plan['ingreso_principal'];
        $ingresosExtra = IngresoExtra::forPlan((int) $plan['id']);
        $items = Item::forPlan((int) $plan['id']);

        $otrosTotal = array_sum(array_map(static fn ($i) => (float) $i['monto'], $ingresosExtra));
        $totalIncomeAll = $principal + $otrosTotal;

        $porCategoria = array_fill_keys(Item::CATEGORIAS, []);
        foreach ($items as $item) {
            $porCategoria[$item['categoria']][] = $item;
        }

        $sum = static function (string $categoria, bool $soloNecesario = false) use ($porCategoria): float {
            $total = 0.0;
            foreach ($porCategoria[$categoria] as $it) {
                if (!$soloNecesario || (bool) $it['es_necesario']) {
                    $total += (float) $it['monto'];
                }
            }
            return $total;
        };

        $dailyDiarios = $sum('diario');
        $dailyDiariosEss = $sum('diario', true);
        $weeklySum = $sum('semanal');
        $weeklySumEss = $sum('semanal', true);
        $dailyRecurring = $dailyDiarios + $weeklySum / 7;
        $dailyRecurringEss = $dailyDiariosEss + $weeklySumEss / 7;

        $oneTimeAll = $sum('fijo') + $sum('compra') + $sum('deuda');
        $oneTimeEss = $sum('fijo', true) + $sum('compra', true) + $sum('deuda', true);

        $totalGastos = $oneTimeAll + $dailyRecurring * 30;
        $totalEsencial = $oneTimeEss + $dailyRecurringEss * 30;

        $dailyAvail = ($totalIncomeAll - $totalGastos) / 30;
        $essDiff = $totalIncomeAll - $totalEsencial;

        return [
            'ingreso_total' => $totalIncomeAll,
            'gasto_total' => $totalGastos,
            'disponible_dia' => $dailyAvail,
            'diferencia_esencial' => $essDiff,
        ];
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM planes WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function latest(): ?array
    {
        $stmt = self::db()->query('SELECT * FROM planes ORDER BY anio DESC, mes DESC, id DESC LIMIT 1');
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $nombre, int $anio, int $mes, float $ingresoPrincipal): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO planes (nombre, anio, mes, ingreso_principal) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nombre !== '' ? $nombre : 'Nuevo plan', $anio, $mes, $ingresoPrincipal]);
        return (int) self::db()->lastInsertId();
    }

    public static function updateIngresoPrincipal(int $id, float $monto): void
    {
        $stmt = self::db()->prepare('UPDATE planes SET ingreso_principal = ? WHERE id = ?');
        $stmt->execute([$monto, $id]);
    }

    public static function rename(int $id, string $nombre): void
    {
        if ($nombre === '') {
            return;
        }
        $stmt = self::db()->prepare('UPDATE planes SET nombre = ? WHERE id = ?');
        $stmt->execute([$nombre, $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = self::db()->prepare('DELETE FROM planes WHERE id = ?');
        $stmt->execute([$id]);
    }
}
