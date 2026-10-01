(function () {
  'use strict';

  const PLAN_ID = window.PLAN_ID;
  const DATA = window.PLAN_DATA || { ingresoPrincipal: 0, ingresosExtra: [], items: [] };

  const CATS = {
    fijo:    { hasDay: true,  items: [] },
    compra:  { hasDay: true,  items: [] },
    semanal: { hasDay: false, items: [] },
    diario:  { hasDay: false, items: [] },
    deuda:   { hasDay: true,  items: [] },
  };

  let otrosIngresos = [];

  function money(n) {
    return 'S/ ' + Math.round(n).toLocaleString('es-PE');
  }

  function debounce(fn, wait) {
    let t;
    return (...args) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...args), wait);
    };
  }

  async function api(method, path, body) {
    try {
      const res = await fetch(path, {
        method,
        headers: { 'Content-Type': 'application/json' },
        body: body !== undefined ? JSON.stringify(body) : undefined,
      });
      if (!res.ok) {
        console.error('Error al guardar', method, path, res.status);
        return null;
      }
      const text = await res.text();
      return text ? JSON.parse(text) : null;
    } catch (err) {
      console.error('Error de red al guardar', method, path, err);
      return null;
    }
  }

  // ---- Ingreso principal --------------------------------------------------

  const saveIncomePrincipal = debounce((valor) => {
    api('PATCH', `/api/planes/${PLAN_ID}/ingreso-principal`, { ingreso_principal: valor });
  }, 500);

  function onIncomeInput(e) {
    recalc();
    saveIncomePrincipal(Math.max(0, parseFloat(e.target.value) || 0));
  }

  // ---- Otros ingresos -------------------------------------------------------

  function addOtroIngreso() {
    const local = { id: null, nombre: '', monto: 0 };
    local._save = api('POST', `/api/planes/${PLAN_ID}/ingresos-extra`, { nombre: '', monto: 0 })
      .then((res) => { if (res) local.id = res.id; });
    otrosIngresos.push(local);
    recalc();
  }

  function removeOtro(i) {
    const o = otrosIngresos[i];
    otrosIngresos.splice(i, 1);
    recalc();
    o._save = o._save.then(() => (o.id ? api('DELETE', `/api/ingresos-extra/${o.id}`) : null));
  }

  const debouncedSaveOtro = debounce((o) => {
    o._save = o._save.then(() => (o.id
      ? api('PATCH', `/api/ingresos-extra/${o.id}`, { nombre: o.nombre, monto: o.monto })
      : null));
  }, 400);

  function updateOtro(i, field, val) {
    if (field === 'monto') val = Math.max(0, parseFloat(val) || 0);
    otrosIngresos[i][field] = val;
    recalc();
    debouncedSaveOtro(otrosIngresos[i]);
  }

  // ---- Ítems por categoría --------------------------------------------------

  function addCatItem(key) {
    const c = CATS[key];
    const item = { id: null, nombre: '', monto: 0, esNecesario: key === 'fijo' || key === 'deuda' };
    if (c.hasDay) item.dia = 1;

    item._save = api('POST', `/api/planes/${PLAN_ID}/items`, {
      categoria: key,
      nombre: item.nombre,
      dia: item.dia ?? null,
      monto: item.monto,
      es_necesario: item.esNecesario,
    }).then((res) => { if (res) item.id = res.id; });

    c.items.push(item);
    renderCat(key);
    recalc();
  }

  function removeCatItem(key, i) {
    const item = CATS[key].items[i];
    CATS[key].items.splice(i, 1);
    renderCat(key);
    recalc();
    item._save = item._save.then(() => (item.id ? api('DELETE', `/api/items/${item.id}`) : null));
  }

  const debouncedSaveItem = debounce((item) => {
    item._save = item._save.then(() => (item.id
      ? api('PATCH', `/api/items/${item.id}`, {
        nombre: item.nombre,
        dia: item.dia ?? null,
        monto: item.monto,
        es_necesario: item.esNecesario,
      })
      : null));
  }, 400);

  function updateCatItem(key, i, field, val) {
    if (field === 'dia') val = Math.min(30, Math.max(1, parseInt(val, 10) || 1));
    if (field === 'monto') val = Math.max(0, parseFloat(val) || 0);
    CATS[key].items[i][field] = val;
    recalc();
    debouncedSaveItem(CATS[key].items[i]);
  }

  function renderCat(key) {
    const c = CATS[key];
    const wrap = document.getElementById('list-' + key);
    wrap.innerHTML = '';

    c.items.forEach((it, i) => {
      const row = document.createElement('div');
      row.className = 'item-row';

      const nameInput = document.createElement('input');
      nameInput.type = 'text';
      nameInput.placeholder = 'Nombre';
      nameInput.value = it.nombre;
      nameInput.addEventListener('input', (e) => updateCatItem(key, i, 'nombre', e.target.value));
      row.appendChild(nameInput);

      if (c.hasDay) {
        const lbl = document.createElement('span');
        lbl.className = 'lbl';
        lbl.textContent = 'Día';
        row.appendChild(lbl);

        const dayInput = document.createElement('input');
        dayInput.type = 'number';
        dayInput.className = 'day';
        dayInput.min = '1';
        dayInput.max = '30';
        dayInput.value = String(it.dia);
        dayInput.addEventListener('input', (e) => updateCatItem(key, i, 'dia', e.target.value));
        row.appendChild(dayInput);
      }

      const soleLbl = document.createElement('span');
      soleLbl.className = 'lbl';
      soleLbl.textContent = 'S/';
      row.appendChild(soleLbl);

      const amtInput = document.createElement('input');
      amtInput.type = 'number';
      amtInput.className = 'amt';
      amtInput.min = '0';
      amtInput.value = String(it.monto);
      amtInput.addEventListener('input', (e) => updateCatItem(key, i, 'monto', e.target.value));
      row.appendChild(amtInput);

      const essLabel = document.createElement('label');
      essLabel.className = 'ess';
      const essCheckbox = document.createElement('input');
      essCheckbox.type = 'checkbox';
      essCheckbox.checked = !!it.esNecesario;
      essCheckbox.addEventListener('change', (e) => updateCatItem(key, i, 'esNecesario', e.target.checked));
      essLabel.appendChild(essCheckbox);
      essLabel.appendChild(document.createTextNode('Necesario'));
      row.appendChild(essLabel);

      const rmBtn = document.createElement('button');
      rmBtn.type = 'button';
      rmBtn.className = 'rmv';
      rmBtn.textContent = 'Eliminar';
      rmBtn.addEventListener('click', () => removeCatItem(key, i));
      row.appendChild(rmBtn);

      wrap.appendChild(row);
    });
  }

  // ---- Cálculo y tarjetas -----------------------------------------------------

  function sumAmount(key, essOnly) {
    return CATS[key].items.reduce((s, it) => s + ((!essOnly || it.esNecesario) ? it.monto : 0), 0);
  }

  function makeCard(cls, label, value, sub) {
    const card = document.createElement('div');
    card.className = 'card ' + cls;

    const p1 = document.createElement('p');
    p1.className = 'label';
    p1.textContent = label;
    card.appendChild(p1);

    const p2 = document.createElement('p');
    p2.className = 'value';
    p2.textContent = value;
    card.appendChild(p2);

    if (sub) {
      const p3 = document.createElement('p');
      p3.className = 'sub2';
      p3.textContent = sub;
      card.appendChild(p3);
    }

    return card;
  }

  function buildCards(totalIncomeAll, totalGastos, dailyAvail, weeklyAvail, essDiff, runOut, balEnd) {
    const wrap = document.getElementById('cards');
    wrap.innerHTML = '';

    wrap.appendChild(makeCard('card-income', 'Ingreso total', money(totalIncomeAll), 'Principal + otros ingresos'));

    otrosIngresos.forEach((o, i) => {
      const card = document.createElement('div');
      card.className = 'card card-otro';

      const rm = document.createElement('button');
      rm.type = 'button';
      rm.className = 'rm';
      rm.textContent = '✕';
      rm.addEventListener('click', () => removeOtro(i));
      card.appendChild(rm);

      const label = document.createElement('p');
      label.className = 'label';
      label.textContent = 'Otro ingreso';
      card.appendChild(label);

      const value = document.createElement('p');
      value.className = 'value';
      value.textContent = money(o.monto);
      card.appendChild(value);

      const sub = document.createElement('p');
      sub.className = 'sub2';
      sub.style.cssText = 'display:flex;gap:4px';

      const nameInput = document.createElement('input');
      nameInput.type = 'text';
      nameInput.placeholder = 'Nombre';
      nameInput.value = o.nombre;
      nameInput.style.cssText = 'width:90px;border:1px solid #cfe;border-radius:6px;padding:2px 5px;font-size:12px';
      nameInput.addEventListener('input', (e) => updateOtro(i, 'nombre', e.target.value));

      const amtInput = document.createElement('input');
      amtInput.type = 'number';
      amtInput.min = '0';
      amtInput.value = String(o.monto);
      amtInput.style.cssText = 'width:70px;border:1px solid #cfe;border-radius:6px;padding:2px 5px;font-size:12px';
      amtInput.addEventListener('input', (e) => updateOtro(i, 'monto', e.target.value));

      sub.appendChild(nameInput);
      sub.appendChild(amtInput);
      card.appendChild(sub);

      wrap.appendChild(card);
    });

    wrap.appendChild(makeCard('card-gastos', 'Gastos totales del mes', money(totalGastos)));
    wrap.appendChild(makeCard('card-diario', 'Disponible por día', money(dailyAvail)));
    wrap.appendChild(makeCard('card-semanal', 'Disponible por semana', money(weeklyAvail)));

    const estadoClass = essDiff < 0 ? 'card-estado-bad' : 'card-estado-ok';
    const estadoTxt = essDiff < 0 ? 'Necesitas prestar' : 'Te sobra tras lo necesario';
    wrap.appendChild(makeCard(estadoClass, estadoTxt, money(Math.abs(essDiff))));

    const durTxt = runOut ? ('Alcanza hasta el día ' + runOut) : 'Alcanza todo el mes';
    const durSub = runOut ? 'Después de ese día no queda dinero' : ('Con ' + money(balEnd) + ' de sobra');
    wrap.appendChild(makeCard('card-duracion', 'Duración del dinero', durTxt, durSub));
  }

  function drawChart(totalIncomeAll, dailyAvail, balances, oneTimeDays) {
    const svg = document.getElementById('chart');
    const x0 = 60, x1 = 1160, y0 = 250, y1 = 30;
    const maxVal = Math.max(totalIncomeAll, 1);
    const xFor = (d) => x0 + (x1 - x0) * (d / 30);
    const yFor = (v) => y0 - (y0 - y1) * (Math.max(0, Math.min(maxVal, v)) / maxVal);

    const parts = [];
    const ranges = [[1, 7, 'Semana 1'], [8, 14, 'Semana 2'], [15, 21, 'Semana 3'], [22, 30, 'Semana 4']];
    ranges.forEach((r) => {
      const days = r[1] - r[0] + 1;
      const barVal = Math.max(0, dailyAvail) * days;
      const xs = xFor(r[0] - 1), xe = xFor(r[1]);
      const yb = yFor(barVal);
      parts.push(`<rect x="${xs}" y="${yb}" width="${xe - xs - 4}" height="${y0 - yb}" fill="#ffd9a8" rx="6"/>`);
      parts.push(`<text x="${(xs + xe) / 2}" y="${yb - 8}" font-size="13" text-anchor="middle" fill="#8a5a1a" font-weight="700">${money(barVal)}</text>`);
      parts.push(`<text x="${(xs + xe) / 2}" y="${y0 + 22}" font-size="12" text-anchor="middle" fill="#999">${r[2]}</text>`);
    });

    parts.push(`<line x1="${x0}" y1="${y0}" x2="${x1}" y2="${y0}" stroke="#ddd" stroke-width="1"/>`);
    parts.push(`<line x1="${x0}" y1="${y1}" x2="${x0}" y2="${y0}" stroke="#ddd" stroke-width="1"/>`);
    parts.push(`<text x="20" y="${y1 + 6}" font-size="12" fill="#888">${money(totalIncomeAll)}</text>`);
    parts.push(`<text x="30" y="${y0 + 4}" font-size="12" fill="#888">0</text>`);

    let linePath = `M ${xFor(0)} ${yFor(balances[0])}`;
    for (let d = 1; d <= 30; d++) linePath += ` L ${xFor(d)} ${yFor(balances[d])}`;
    parts.push(`<path d="${linePath}" fill="none" stroke="#0F6E56" stroke-width="3"/>`);

    oneTimeDays.forEach((o) => {
      const cx = xFor(o.dia), cy = yFor(balances[o.dia]);
      parts.push(`<circle cx="${cx}" cy="${cy}" r="5" fill="#e26060"><title>${escapeXml(o.nombre)}: ${money(o.monto)}</title></circle>`);
    });

    [1, 5, 10, 15, 20, 25, 30].forEach((d) => {
      parts.push(`<text x="${xFor(d)}" y="${y0 + 40}" font-size="12" text-anchor="middle" fill="#aaa">Día ${d}</text>`);
    });

    svg.innerHTML = parts.join('');
  }

  function escapeXml(s) {
    return String(s).replace(/[<>&"']/g, (c) => ({
      '<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;', "'": '&apos;',
    }[c]));
  }

  function recalc() {
    const principalInput = document.getElementById('income-principal');
    const principal = Math.max(0, parseFloat(principalInput.value) || 0);
    const otrosTotal = otrosIngresos.reduce((s, o) => s + o.monto, 0);
    const totalIncomeAll = principal + otrosTotal;

    const dailyDiarios = sumAmount('diario');
    const dailyDiariosEss = sumAmount('diario', true);
    const weeklySum = sumAmount('semanal');
    const weeklySumEss = sumAmount('semanal', true);
    const dailyRecurring = dailyDiarios + weeklySum / 7;
    const dailyRecurringEss = dailyDiariosEss + weeklySumEss / 7;

    const oneTimeAll = sumAmount('fijo') + sumAmount('compra') + sumAmount('deuda');
    const oneTimeEss = sumAmount('fijo', true) + sumAmount('compra', true) + sumAmount('deuda', true);

    const totalGastos = oneTimeAll + dailyRecurring * 30;
    const totalEsencial = oneTimeEss + dailyRecurringEss * 30;

    const dailyAvail = (totalIncomeAll - totalGastos) / 30;
    const weeklyAvail = dailyAvail * 7;
    const essDiff = totalIncomeAll - totalEsencial;

    let bal = totalIncomeAll;
    const balances = [bal];
    let runOut = null;
    const oneTimeDays = [];
    ['fijo', 'compra', 'deuda'].forEach((key) => {
      CATS[key].items.forEach((it) => {
        if (it.monto > 0) oneTimeDays.push({ dia: it.dia, nombre: it.nombre || key, monto: it.monto });
      });
    });

    for (let d = 1; d <= 30; d++) {
      bal -= dailyRecurring;
      oneTimeDays.forEach((o) => { if (o.dia === d) bal -= o.monto; });
      balances.push(bal);
      if (runOut === null && bal <= 0) runOut = d;
    }

    buildCards(totalIncomeAll, totalGastos, dailyAvail, weeklyAvail, essDiff, runOut, balances[30]);

    const banner = document.getElementById('banner');
    if (essDiff < 0) {
      banner.className = 'rounded-2xl px-5 py-3.5 mb-5 text-sm sm:text-base font-semibold banner-bad';
      banner.textContent = 'Con solo lo necesario ya te falta ' + money(Math.abs(essDiff)) + ' este mes. Esa es la cantidad que necesitarías prestar o conseguir de algún otro lado para cubrir lo esencial.';
    } else {
      banner.className = 'rounded-2xl px-5 py-3.5 mb-5 text-sm sm:text-base font-semibold banner-ok';
      banner.textContent = 'Cubriendo todo lo necesario, te quedan ' + money(essDiff) + ' libres en el mes para lo demás.';
    }

    drawChart(totalIncomeAll, dailyAvail, balances, oneTimeDays);
  }

  function exportPDF() {
    const el = document.getElementById('sheet');
    window.html2pdf().set({
      margin: 8,
      filename: 'plan-del-mes.pdf',
      html2canvas: { scale: 2 },
      jsPDF: { unit: 'mm', format: 'a3', orientation: 'landscape' },
    }).from(el).save();
  }

  function init() {
    DATA.items.forEach((it) => {
      const cat = CATS[it.categoria];
      if (!cat) return;
      cat.items.push({
        id: it.id,
        nombre: it.nombre,
        dia: it.dia,
        monto: it.monto,
        esNecesario: it.esNecesario,
        _save: Promise.resolve(),
      });
    });

    otrosIngresos = DATA.ingresosExtra.map((o) => ({
      id: o.id, nombre: o.nombre, monto: o.monto, _save: Promise.resolve(),
    }));

    document.getElementById('income-principal').addEventListener('input', onIncomeInput);
    document.getElementById('addIncomeBtn').addEventListener('click', addOtroIngreso);
    document.getElementById('exportBtn').addEventListener('click', exportPDF);
    document.querySelectorAll('.addBtn').forEach((btn) => {
      btn.addEventListener('click', () => addCatItem(btn.dataset.cat));
    });

    const switcher = document.getElementById('plan-switcher');
    if (switcher) {
      switcher.addEventListener('change', () => { window.location.href = '/plan/' + switcher.value; });
    }

    const dialog = document.getElementById('new-plan-dialog');
    const newPlanBtn = document.getElementById('newPlanBtn');
    const cancelNewPlanBtn = document.getElementById('cancelNewPlanBtn');
    if (dialog && newPlanBtn) {
      newPlanBtn.addEventListener('click', () => dialog.showModal());
      cancelNewPlanBtn.addEventListener('click', () => dialog.close());
    }

    Object.keys(CATS).forEach(renderCat);
    recalc();
  }

  document.addEventListener('DOMContentLoaded', init);
})();
