<?php
/** @var array $anios */

$mesesEs = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Setiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
];

function s($n) { return 'S/ ' . number_format((float) $n, 0, ',', '.'); }
?>
<div class="w-full bg-white rounded-3xl p-4 sm:p-7">

  <div class="flex items-center justify-between gap-3 mb-6 flex-wrap">
    <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-900">Historial de meses</h1>
    <a href="/" class="text-sm font-semibold bg-neutral-100 hover:bg-neutral-200 rounded-lg px-3 py-1.5">
      &larr; Volver al plan actual
    </a>
  </div>

  <?php if (!$anios): ?>
    <p class="text-neutral-500">Todavía no hay planes guardados.</p>
  <?php endif; ?>

  <?php foreach ($anios as $bloque): ?>
    <div class="mb-8">
      <h2 class="text-lg font-bold text-neutral-800 mb-3"><?= (int) $bloque['anio'] ?></h2>

      <div class="overflow-x-auto rounded-2xl border border-neutral-100">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-neutral-50 text-neutral-500 text-left">
              <th class="px-4 py-2.5 font-semibold">Mes</th>
              <th class="px-4 py-2.5 font-semibold">Ingreso total</th>
              <th class="px-4 py-2.5 font-semibold">Gastos totales</th>
              <th class="px-4 py-2.5 font-semibold">Disponible / día</th>
              <th class="px-4 py-2.5 font-semibold">Estado</th>
              <th class="px-4 py-2.5"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($bloque['filas'] as $fila): $plan = $fila['plan']; $r = $fila['resumen']; ?>
              <tr class="border-t border-neutral-100">
                <td class="px-4 py-2.5 font-medium text-neutral-800">
                  <?= $mesesEs[(int) $plan['mes']] ?? $plan['mes'] ?>
                  <span class="text-neutral-400 font-normal">· <?= htmlspecialchars($plan['nombre']) ?></span>
                </td>
                <td class="px-4 py-2.5"><?= s($r['ingreso_total']) ?></td>
                <td class="px-4 py-2.5"><?= s($r['gasto_total']) ?></td>
                <td class="px-4 py-2.5"><?= s($r['disponible_dia']) ?></td>
                <td class="px-4 py-2.5">
                  <?php if ($r['diferencia_esencial'] < 0): ?>
                    <span class="inline-block text-xs font-semibold px-2 py-1 rounded-full card-estado-bad">Necesita prestar</span>
                  <?php else: ?>
                    <span class="inline-block text-xs font-semibold px-2 py-1 rounded-full card-estado-ok">Cubierto</span>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-2.5 text-right">
                  <a href="/plan/<?= (int) $plan['id'] ?>" class="text-neutral-600 hover:text-neutral-900 font-semibold">Ver &rarr;</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="border-t border-neutral-200 bg-neutral-50 font-semibold">
              <td class="px-4 py-2.5">Total <?= (int) $bloque['anio'] ?></td>
              <td class="px-4 py-2.5"><?= s($bloque['totales']['ingreso_total']) ?></td>
              <td class="px-4 py-2.5"><?= s($bloque['totales']['gasto_total']) ?></td>
              <td class="px-4 py-2.5" colspan="3"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  <?php endforeach; ?>
</div>
