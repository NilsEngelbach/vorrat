/* Notvorrat: kleine Helfer, die Seite funktioniert auch ohne. */
(() => {
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const store = {
    get(k) { try { return JSON.parse(localStorage.getItem(k) || 'null'); } catch { return null; } },
    set(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch { /* privat, egal */ } },
  };
  const parseNum = (v) => {
    const s = String(v || '').trim().replace(/\s/g, '');
    if (!s) return NaN;
    return Number(s.includes(',') ? s.replace(/\./g, '').replace(',', '.') : s);
  };
  const fmtNum = (n) => (Math.round(n * 1000) / 1000).toString().replace('.', ',');

  // Zeichnung und Stückliste: gleiche Positionsnummer leuchtet gemeinsam.
  const linked = $$('[data-pos]');
  if (linked.length) {
    const light = (pos, on) => linked.forEach((el) => { if (el.dataset.pos === pos) el.classList.toggle('is-lit', on); });
    linked.forEach((el) => {
      el.addEventListener('mouseenter', () => light(el.dataset.pos, true));
      el.addEventListener('mouseleave', () => light(el.dataset.pos, false));
      el.addEventListener('focusin', () => light(el.dataset.pos, true));
      el.addEventListener('focusout', () => light(el.dataset.pos, false));
    });
  }

  // Nach dem Einlagern: frisch gefüllten Artikel im Regal zeigen
  const fresh = document.querySelector('.slot.is-new');
  if (fresh) {
    fresh.scrollIntoView({ block: 'center', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
    history.replaceState(null, '', location.pathname);
  }

  // Suchfilter
  $$('[data-filter]').forEach((input) => {
    const items = $$(input.dataset.filter);
    const empty = document.querySelector('.filter-empty');
    input.addEventListener('input', () => {
      const q = input.value.trim().toLowerCase();
      let shown = 0;
      items.forEach((it) => {
        const hit = !q || (it.dataset.name || it.textContent.toLowerCase()).includes(q);
        it.hidden = !hit;
        if (hit) shown++;
      });
      // Leere Gruppen ausblenden
      $$('.stock-group, .pick-group').forEach((g) => {
        g.hidden = !!q && !$$(input.dataset.filter, g).some((it) => !it.hidden);
      });
      if (empty) empty.hidden = shown > 0;
    });
  });

  // Zähler mit Plus/Minus
  $$('.stepper').forEach((st) => {
    const input = st.querySelector('input');
    const min = Number(input.dataset.min ?? 0);
    $$('[data-step]', st).forEach((b) => b.addEventListener('click', () => {
      const v = Math.max(min, (parseInt(input.value, 10) || 0) + Number(b.dataset.step));
      input.value = v;
      input.dispatchEvent(new Event('input', { bubbles: true }));
    }));
  });

  // Einlagern: Packungen ↔ Menge, MHD-Vorschläge
  const form = document.querySelector('.stockup');
  if (form) {
    const meta = JSON.parse(form.dataset.meta || '{}');
    const qty = form.querySelector('[data-qty]');
    const packs = form.querySelector('[data-packs]');
    const packsField = form.querySelector('[data-packs-field]');
    const unit = form.querySelector('[data-unit]');
    const label = form.querySelector('[data-pack-label]');
    const selected = form.querySelector('[data-selected]');
    const mhd = form.querySelector('[data-mhd]');
    const typical = form.querySelector('[data-typical]');
    let current = null;
    let qtyTouched = qty.value !== '';

    const addMonths = (m) => {
      const d = new Date();
      d.setMonth(d.getMonth() + m);
      return d.toISOString().slice(0, 10);
    };
    const syncQty = () => {
      if (!current || !current.pack || qtyTouched) return;
      const p = parseNum(packs.value);
      if (p > 0) qty.value = fmtNum(p * current.pack);
    };
    const select = (id, initial) => {
      current = meta[id] || null;
      if (!current) return;
      selected.textContent = current.name;
      selected.classList.remove('is-empty');
      unit.textContent = current.unit;
      packsField.hidden = !current.pack;
      label.textContent = current.packLabel ? `(${current.packLabel})` : '';
      if (typical) {
        typical.hidden = !current.months;
        typical.dataset.months = current.months;
        typical.textContent = current.months ? `typisch ${current.months >= 12 && current.months % 12 === 0 ? current.months / 12 + (current.months === 12 ? ' Jahr' : ' Jahre') : current.months + ' Mon.'}` : '';
      }
      if (initial) {
        syncQty();
      } else {
        qtyTouched = false;
        packs.value = 1;
        syncQty();
        if (window.matchMedia('(max-width: 1120px)').matches) {
          form.querySelector('.entry').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    };

    $$('input[name="item_id"]', form).forEach((r) => r.addEventListener('change', () => select(r.value, false)));
    const checked = form.querySelector('input[name="item_id"]:checked');
    if (checked) select(checked.value, true); else selected.classList.add('is-empty');

    packs.addEventListener('input', syncQty);
    qty.addEventListener('input', () => { qtyTouched = qty.value !== ''; });

    $$('[data-mhd-chips] .chip', form).forEach((c) => c.addEventListener('click', () => {
      const m = Number(c.dataset.months);
      mhd.value = m > 0 ? addMonths(m) : '';
      $$('[data-mhd-chips] .chip', form).forEach((o) => o.classList.toggle('is-on', o === c));
      if (m > 0) mhd.focus();
    }));
    mhd.addEventListener('input', () => $$('[data-mhd-chips] .chip', form).forEach((o) => o.classList.remove('is-on')));
  }

  // Planung: Reichweite und Markierungen rechnen beim Bearbeiten mit (gleiche Formel wie coverage())
  const plan = document.querySelector('.plan-form[data-ref]');
  if (plan) {
    const ref = JSON.parse(plan.dataset.ref);
    const rows = $$('.plan-table tbody tr', plan);
    const outs = $$('[data-cover]');
    const fmt = (n) => (Math.round(n * 10) / 10).toLocaleString('de-DE', { maximumFractionDigits: 1 });
    const update = () => {
      let kcal = 0, protein = 0, fat = 0, carbs = 0, water = 0;
      rows.forEach((tr) => {
        const on = tr.querySelector('input[type="checkbox"]').checked;
        const v = parseNum(tr.querySelector('.input-small').value);
        const rec = Number(tr.dataset.rec);
        tr.classList.toggle('is-off', !on);
        tr.classList.toggle('is-custom-target', !Number.isNaN(v) && Math.abs(v - rec) >= 0.0051);
        if (!on || !tr.dataset.kind) return;
        const q = Number.isNaN(v) ? rec : Math.max(0, v);  // leer = Empfehlung, wie beim Speichern
        const per100 = q * 10;
        kcal += per100 * tr.dataset.kcal;
        protein += per100 * tr.dataset.protein;
        fat += per100 * tr.dataset.fat;
        carbs += per100 * tr.dataset.carbs;
        if (tr.dataset.kind === 'drink') water += q;
      });
      const days = {
        food_days: kcal / (ref.kcal * ref.persons),
        water_days: water / (ref.water * ref.heads),
        protein_days: protein / (ref.protein * ref.persons),
        fat_days: fat / (ref.fat * ref.persons),
        carbs_days: carbs / (ref.carbs * ref.persons),
      };
      days.days = Math.min(days.food_days, days.water_days);
      outs.forEach((el) => { el.textContent = fmt(days[el.dataset.cover]); });
    };
    plan.addEventListener('input', update);
    plan.addEventListener('change', update);
  }

  // Einkaufsliste: Haken bleiben auf diesem Gerät gespeichert
  const checks = $$('[data-check]');
  if (checks.length) {
    const key = 'notvorrat.einkauf';
    const state = store.get(key) || {};
    checks.forEach((c) => {
      c.checked = !!state[c.dataset.check];
      c.addEventListener('change', () => { state[c.dataset.check] = c.checked; store.set(key, state); });
    });
    const clear = document.querySelector('[data-clear-checks]');
    if (clear) clear.addEventListener('click', () => { checks.forEach((c) => { c.checked = false; }); store.set(key, {}); });
  }
  const print = document.querySelector('[data-print]');
  if (print) print.addEventListener('click', () => window.print());

  // Rückfrage vor folgenreichen Aktionen
  $$('form[data-confirm]').forEach((f) => f.addEventListener('submit', (e) => {
    if (!window.confirm(f.dataset.confirm)) e.preventDefault();
  }));
})();
