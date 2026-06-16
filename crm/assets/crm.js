/* M2 Platform — CRM JS */
'use strict';

/* Modal helpers */
window.openModal  = id => document.getElementById(id)?.classList.add('open');
window.closeModal = id => document.getElementById(id)?.classList.remove('open');
document.querySelectorAll('.modal-bg').forEach(bg => {
  bg.addEventListener('click', e => { if (e.target === bg) bg.classList.remove('open'); });
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-bg.open').forEach(m => m.classList.remove('open'));
});

/* Row click navigation */
document.querySelectorAll('tr[data-href]').forEach(tr => {
  tr.addEventListener('click', e => {
    if (e.target.closest('a,button,select,input')) return;
    location.href = tr.dataset.href;
  });
});

/* Kanban drag & drop */
(function () {
  let dragged = null;
  document.querySelectorAll('.kanban__card[draggable]').forEach(card => {
    card.addEventListener('dragstart', () => { dragged = card; card.style.opacity = '.4'; });
    card.addEventListener('dragend',   () => { dragged = null; card.style.opacity = '1'; });
  });
  document.querySelectorAll('.kanban__col').forEach(col => {
    col.addEventListener('dragover', e => { e.preventDefault(); col.style.background = '#E3E8EF'; });
    col.addEventListener('dragleave', () => col.style.background = '');
    col.addEventListener('drop', async e => {
      e.preventDefault(); col.style.background = '';
      if (!dragged) return;
      const stage = col.dataset.stage;
      const id = dragged.dataset.id;
      col.querySelector('.kanban__cards')?.appendChild(dragged);
      try {
        await fetch('api/lead-stage.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id, stage })
        });
        location.reload();
      } catch { alert('Kunde inte uppdatera.'); }
    });
  });
})();

/* AJAX form helper */
window.ajaxForm = function (formId, url, onSuccess) {
  const f = document.getElementById(formId);
  if (!f) return;
  f.addEventListener('submit', async e => {
    e.preventDefault();
    const res = await fetch(url, { method: 'POST', body: new FormData(f) });
    const data = await res.json();
    if (data.success) (onSuccess || (() => location.reload()))(data);
    else alert(data.message || 'Fel');
  });
};

/* Quote item calculator */
window.recalcQuote = function () {
  let work = 0, material = 0;
  document.querySelectorAll('.qi-row').forEach(row => {
    const qty   = parseFloat(row.querySelector('.qi-qty')?.value) || 0;
    const price = parseFloat(row.querySelector('.qi-price')?.value) || 0;
    const isWork = row.querySelector('.qi-work')?.checked;
    const line = qty * price;
    const lineEl = row.querySelector('.qi-line');
    if (lineEl) lineEl.textContent = line.toLocaleString('sv-SE') + ' kr';
    if (isWork) work += line; else material += line;
  });
  const subtotal = work + material;
  const vat = subtotal * 0.25;
  const rot = Math.min(work * 1.25 * 0.30, 50000);
  const total = subtotal + vat - rot;
  const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v.toLocaleString('sv-SE', {maximumFractionDigits:0}) + ' kr'; };
  set('q-subtotal', subtotal); set('q-vat', vat); set('q-rot', -rot); set('q-total', total);
};
document.querySelectorAll('.qi-qty,.qi-price,.qi-work').forEach(el =>
  el.addEventListener('input', recalcQuote));
if (document.querySelector('.qi-row')) recalcQuote();

/* Add quote item row */
window.addQuoteItem = function () {
  const tbody = document.getElementById('qi-body');
  if (!tbody) return;
  const n = tbody.querySelectorAll('.qi-row').length;
  const tr = document.createElement('tr');
  tr.className = 'qi-row';
  tr.innerHTML = `
    <td><input class="fi" name="items[${n}][description]" placeholder="Beskrivning" required></td>
    <td><input class="fi qi-qty" name="items[${n}][qty]" type="number" step="0.1" value="1" style="width:70px"></td>
    <td><input class="fi" name="items[${n}][unit]" value="st" style="width:64px"></td>
    <td><input class="fi qi-price" name="items[${n}][unit_price]" type="number" step="1" value="0" style="width:110px"></td>
    <td style="text-align:center"><input type="checkbox" class="qi-work" name="items[${n}][is_work]" value="1" checked></td>
    <td class="qi-line" style="text-align:right;font-weight:600;white-space:nowrap">0 kr</td>
    <td><button type="button" class="btn btn--danger btn--sm" onclick="this.closest('tr').remove();recalcQuote()">✕</button></td>`;
  tbody.appendChild(tr);
  tr.querySelectorAll('.qi-qty,.qi-price,.qi-work').forEach(el => el.addEventListener('input', recalcQuote));
  recalcQuote();
};
