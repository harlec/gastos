<?php
/** @var array $plan */
/** @var array $planes */
/** @var array $ingresosExtra */
/** @var array $items */

$mesesEs = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Setiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
];
?>
<div id="sheet" class="max-w-6xl mx-auto bg-white rounded-3xl p-4 sm:p-7">

  <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-4">
    <div>
      <div class="flex items-center gap-3 flex-wrap">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-900">Tu plan del mes</h1>

        <select id="plan-switcher" class="no-print text-sm border border-neutral-200 rounded-lg px-2 py-1">
          <?php foreach ($planes as $p): ?>
            <option value="<?= (int) $p['id'] ?>" <?= $p['id'] == $plan['id'] ? 'selected' : '' ?>>
              <?= $mesesEs[(int) $p['mes']] ?? $p['mes'] ?> <?= (int) $p['anio'] ?> · <?= htmlspecialchars($p['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <button type="button" id="newPlanBtn"
                class="no-print text-xs font-semibold bg-neutral-100 hover:bg-neutral-200 rounded-lg px-3 py-1.5">
          + Nuevo plan
        </button>

        <a href="/historial" class="no-print text-xs font-semibold bg-neutral-100 hover:bg-neutral-200 rounded-lg px-3 py-1.5">
          Historial
        </a>
      </div>
      <p class="text-neutral-400 text-sm mt-1">Cuánto puedes gastar por día y por semana, y cuánto necesitas para llegar a fin de mes</p>
    </div>

    <div class="flex items-center gap-2 flex-wrap no-print">
      <label for="income-principal" class="text-sm text-neutral-600">Ingreso principal (S/)</label>
      <input type="number" id="income-principal" value="<?= htmlspecialchars((string) $plan['ingreso_principal']) ?>"
             min="0" step="10"
             class="text-xl font-bold border border-neutral-200 rounded-xl px-3 py-2 w-36">
      <button type="button" id="addIncomeBtn" class="bg-lime-100 text-lime-800 font-semibold text-sm rounded-xl px-4 py-2 hover:bg-lime-200">
        + Otro ingreso
      </button>
      <button type="button" id="exportBtn" class="bg-neutral-900 text-white font-semibold text-sm rounded-xl px-5 py-2.5 hover:bg-neutral-700">
        Exportar a PDF
      </button>
    </div>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-4" id="cards"></div>

  <p id="banner" class="rounded-2xl px-5 py-3.5 mb-5 text-sm sm:text-base font-semibold"></p>

  <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
    <div class="bg-neutral-50 rounded-2xl p-4">
      <h2 class="text-base font-bold text-neutral-900 mb-2.5">Pagos fijos con fecha</h2>
      <div id="list-fijo" class="space-y-2"></div>
      <button type="button" class="addBtn no-print" data-cat="fijo">+ Agregar pago fijo</button>
    </div>
    <div class="bg-neutral-50 rounded-2xl p-4">
      <h2 class="text-base font-bold text-neutral-900 mb-2.5">Compras o gastos del mes</h2>
      <div id="list-compra" class="space-y-2"></div>
      <button type="button" class="addBtn no-print" data-cat="compra">+ Agregar compra o gasto</button>
    </div>
    <div class="bg-neutral-50 rounded-2xl p-4">
      <h2 class="text-base font-bold text-neutral-900 mb-2.5">Gastos semanales</h2>
      <div id="list-semanal" class="space-y-2"></div>
      <button type="button" class="addBtn no-print" data-cat="semanal">+ Agregar gasto semanal</button>
    </div>
    <div class="bg-neutral-50 rounded-2xl p-4">
      <h2 class="text-base font-bold text-neutral-900 mb-2.5">Gastos diarios</h2>
      <div id="list-diario" class="space-y-2"></div>
      <button type="button" class="addBtn no-print" data-cat="diario">+ Agregar gasto diario</button>
    </div>
    <div class="bg-neutral-50 rounded-2xl p-4">
      <h2 class="text-base font-bold text-neutral-900 mb-2.5">Deudas y préstamos pendientes</h2>
      <div id="list-deuda" class="space-y-2"></div>
      <button type="button" class="addBtn no-print" data-cat="deuda">+ Agregar deuda o préstamo</button>
    </div>
  </div>

  <h2 class="text-base font-bold text-neutral-900 mb-2.5">Semana a semana y día a día (1 al 30)</h2>
  <svg id="chart" viewBox="0 0 1200 300" class="w-full h-auto"></svg>
  <div class="flex flex-wrap gap-5 text-xs text-neutral-500 mt-2.5">
    <span><span class="inline-block w-3 h-3 rounded-sm mr-1 align-middle" style="background:#ffd9a8"></span>Presupuesto repartido parejo por semana</span>
    <span><span class="inline-block w-3.5 h-0.5 mr-1 align-middle" style="background:#0F6E56"></span>Tu saldo real día a día</span>
    <span><span class="inline-block w-2.5 h-2.5 rounded-full mr-1 align-middle" style="background:#e26060"></span>Día con pago o deuda</span>
  </div>
</div>

<dialog id="new-plan-dialog" class="rounded-2xl p-0 backdrop:bg-black/40 no-print">
  <form method="post" action="/plan" class="p-6 w-80 space-y-3">
    <h3 class="text-lg font-bold">Nuevo plan mensual</h3>
    <div class="grid grid-cols-2 gap-2">
      <label class="block text-sm text-neutral-600">Mes
        <select name="mes" class="mt-1 w-full border border-neutral-200 rounded-lg px-3 py-2">
          <?php foreach ($mesesEs as $num => $nombreMes): ?>
            <option value="<?= $num ?>" <?= $num === (int) date('n') ? 'selected' : '' ?>><?= $nombreMes ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="block text-sm text-neutral-600">Año
        <input type="number" name="anio" value="<?= (int) date('Y') ?>" class="mt-1 w-full border border-neutral-200 rounded-lg px-3 py-2">
      </label>
    </div>
    <label class="block text-sm text-neutral-600">Nombre (opcional)
      <input name="nombre" class="mt-1 w-full border border-neutral-200 rounded-lg px-3 py-2" placeholder="Se usa 'Mes Año' si lo dejas vacío">
    </label>
    <label class="block text-sm text-neutral-600">Ingreso principal (S/)
      <input type="number" name="ingreso_principal" value="0" min="0" class="mt-1 w-full border border-neutral-200 rounded-lg px-3 py-2">
    </label>
    <div class="flex justify-end gap-2 pt-2">
      <button type="button" id="cancelNewPlanBtn" class="px-3 py-2 text-sm">Cancelar</button>
      <button type="submit" class="bg-neutral-900 text-white rounded-lg px-4 py-2 text-sm font-semibold">Crear</button>
    </div>
  </form>
</dialog>

<script>
  window.PLAN_ID = <?= (int) $plan['id'] ?>;
  window.PLAN_DATA = {
    ingresoPrincipal: <?= json_encode((float) $plan['ingreso_principal']) ?>,
    ingresosExtra: <?= json_encode(array_map(static fn ($i) => [
        'id' => (int) $i['id'],
        'nombre' => $i['nombre'],
        'monto' => (float) $i['monto'],
    ], $ingresosExtra)) ?>,
    items: <?= json_encode(array_map(static fn ($i) => [
        'id' => (int) $i['id'],
        'categoria' => $i['categoria'],
        'nombre' => $i['nombre'],
        'dia' => $i['dia'] !== null ? (int) $i['dia'] : null,
        'monto' => (float) $i['monto'],
        'esNecesario' => (bool) $i['es_necesario'],
    ], $items)) ?>
  };
</script>
