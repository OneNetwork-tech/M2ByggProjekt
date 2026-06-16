/* ============================================================
   M2 BYGG TEAM AB — MAIN JS
   Nav scroll, reveal, counter, FAQ, before/after slider,
   gallery filter, lightbox, form AJAX, smooth scroll.
   ============================================================ */
'use strict';

const $ = s => document.querySelector(s);
const $$ = s => [...document.querySelectorAll(s)];
const on = (el, ev, fn, opts) => el?.addEventListener(ev, fn, opts);

/* ── NAV ───────────────────────────────────────────────── */
(function() {
  const nav = $('.nav');
  const burger = $('.nav__burger');
  const mob = $('.nav__mobile');
  const overlay = $('.nav__overlay');
  const mobToggle = $('.nav__mobile-toggle');
  const mobSub = $('.nav__mobile-sub');

  if (!nav) return;
  const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 60);
  on(window, 'scroll', onScroll, { passive: true });
  onScroll();

  function openMob()  {
    burger?.classList.add('open');
    mob?.classList.add('open');
    overlay?.classList.add('open');
    mob?.setAttribute('aria-modal', 'true');
    document.body.style.overflow = 'hidden';
    mob?.querySelector('a, button')?.focus();
  }
  function closeMob() {
    burger?.classList.remove('open');
    mob?.classList.remove('open');
    overlay?.classList.remove('open');
    mob?.removeAttribute('aria-modal');
    document.body.style.overflow = '';
    burger?.focus();
  }

  on(burger, 'click', () => mob?.classList.contains('open') ? closeMob() : openMob());
  on(overlay, 'click', closeMob);
  on(document, 'keydown', e => { if (e.key === 'Escape') { closeMob(); closeDropdowns(); } });

  on(mobToggle, 'click', function() {
    const isOpen = mobSub?.style.display === 'block';
    if (mobSub) mobSub.style.display = isOpen ? 'none' : 'block';
    const icon = this.querySelector('svg');
    if (icon) icon.style.transform = isOpen ? '' : 'rotate(180deg)';
  });

  /* Keyboard-accessible dropdown — opens on focus/Enter/Space, closes on Escape/blur */
  function closeDropdowns() {
    $$('.nav__item').forEach(item => {
      const dd = item.querySelector('.nav__dropdown');
      if (dd) { dd.style.opacity = ''; dd.style.visibility = ''; dd.style.transform = ''; }
    });
  }

  $$('.nav__item').forEach(item => {
    const trigger = item.querySelector(':scope > a');
    const dd = item.querySelector('.nav__dropdown');
    if (!trigger || !dd) return;

    trigger.setAttribute('aria-haspopup', 'true');
    trigger.setAttribute('aria-expanded', 'false');

    function openDd() {
      closeDropdowns();
      dd.style.opacity = '1';
      dd.style.visibility = 'visible';
      dd.style.transform = 'translateY(0)';
      trigger.setAttribute('aria-expanded', 'true');
    }
    function closeDd() {
      dd.style.opacity = '';
      dd.style.visibility = '';
      dd.style.transform = '';
      trigger.setAttribute('aria-expanded', 'false');
    }

    on(trigger, 'keydown', e => {
      if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
        e.preventDefault();
        const isOpen = dd.style.visibility === 'visible';
        isOpen ? closeDd() : openDd();
        if (!isOpen) dd.querySelector('a')?.focus();
      }
    });

    on(trigger, 'focus', openDd);

    on(item, 'focusout', e => {
      if (!item.contains(e.relatedTarget)) closeDd();
    });

    const ddLinks = $$('a', dd);
    ddLinks.forEach((link, i) => {
      on(link, 'keydown', e => {
        if (e.key === 'ArrowDown') { e.preventDefault(); ddLinks[i + 1]?.focus(); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); i === 0 ? trigger.focus() : ddLinks[i - 1]?.focus(); }
        if (e.key === 'Escape')    { closeDd(); trigger.focus(); }
        if (e.key === 'Tab' && i === ddLinks.length - 1 && !e.shiftKey) closeDd();
      });
    });
  });
})();

/* ── HERO PREMIUM ANIMATION ───────────────────────────────
   1. Ken Burns entrance (bg zooms in slowly)
   2. Word-by-word headline reveal (clips from below)
   3. Sub + buttons + trust fade up sequentially
   4. Parallax on scroll
   ──────────────────────────────────────────────────────── */
(function() {
  const hero   = document.getElementById('hero');
  const bg     = hero?.querySelector('.hero__bg');
  if (!bg || !hero) return;

  // 1. Ken Burns: start zoomed, ease to scale(1) once image loads
  const img = new Image();
  img.src = bg.style.backgroundImage.replace(/url\(['"]?([^'"]+)['"]?\)/, '$1');
  const startBg = () => { bg.classList.add('loaded'); };
  img.complete ? startBg() : (img.onload = startBg);

  // 2. Word-by-word stagger
  const words = hero.querySelectorAll('.hero__word-inner');
  const baseDelay = 80; // ms between words

  // Trigger after a tiny RAF to ensure layout
  requestAnimationFrame(() => {
    setTimeout(() => {
      hero.classList.add('hero--visible');
      words.forEach((w, i) => {
        w.style.transitionDelay = (i * baseDelay) + 'ms';
      });
    }, 120);
  });

  // 3. Parallax (desktop only, respects reduced-motion)
  if (window.innerWidth < 768) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  let ticking = false;
  window.addEventListener('scroll', () => {
    if (ticking) return;
    requestAnimationFrame(() => {
      const y = window.scrollY;
      const h = hero.offsetHeight;
      if (y < h && bg.classList.contains('loaded')) {
        bg.style.transform = 'scale(1.04) translateY(' + (y * 0.18) + 'px)';
      }
      ticking = false;
    });
    ticking = true;
  }, { passive: true });
})();

/* ── SCROLL REVEAL ─────────────────────────────────────── */
(function() {
  const els = $$('.reveal, .animate-in');
  if (!els.length) return;
  $$('.reveal-group').forEach(g => {
    $$('.reveal', g).forEach((el, i) => { el.style.transitionDelay = (i * 75) + 'ms'; });
  });
  const obs = new IntersectionObserver(entries => {
    entries.forEach(e => { if (!e.isIntersecting) return; e.target.classList.add('visible'); obs.unobserve(e.target); });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
  els.forEach(el => obs.observe(el));
})();

/* ── COUNTER ANIMATION ─────────────────────────────────── */
(function() {
  const counters = $$('.counter__num[data-target]');
  if (!counters.length) return;
  const easeOut = t => 1 - Math.pow(1 - t, 3);
  const obs = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      obs.unobserve(entry.target);
      const el = entry.target;
      const target = parseFloat(el.dataset.target);
      const suffix = el.dataset.suffix || '';
      const isDecimal = target % 1 !== 0;
      const duration = 1800;
      const start = performance.now();
      const tick = now => {
        const p = Math.min((now - start) / duration, 1);
        const val = easeOut(p) * target;
        el.textContent = (isDecimal ? val.toFixed(1) : Math.round(val).toLocaleString('sv-SE')) + suffix;
        if (p < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    });
  }, { threshold: 0.5 });
  counters.forEach(c => obs.observe(c));
})();

/* ── FAQ ACCORDION ─────────────────────────────────────── */
(function() {
  $$('.faq-item').forEach(item => {
    const btn = item.querySelector('.faq-q');
    const ans = item.querySelector('.faq-a');
    if (!btn || !ans) return;
    on(btn, 'click', () => {
      const open = item.classList.contains('open');
      $$('.faq-item.open').forEach(o => { o.classList.remove('open'); o.querySelector('.faq-a').style.maxHeight = '0'; });
      if (!open) { item.classList.add('open'); ans.style.maxHeight = ans.scrollHeight + 'px'; }
    });
  });
})();

/* ── BEFORE / AFTER SLIDER ─────────────────────────────── */
(function() {
  $$('.ba-wrap').forEach(wrap => {
    const handle = wrap.querySelector('.ba-handle');
    const after = wrap.querySelector('.ba-after');
    if (!handle || !after) return;

    wrap.setAttribute('tabindex', '0');
    wrap.setAttribute('role', 'slider');
    wrap.setAttribute('aria-label', 'Före/efter-jämförelse – använd piltangenter för att justera');
    wrap.setAttribute('aria-valuemin', '0');
    wrap.setAttribute('aria-valuemax', '100');
    wrap.setAttribute('aria-valuenow', '50');

    let currentPct = 50;
    let dragging = false;

    const setPos = (x) => {
      const r = wrap.getBoundingClientRect();
      currentPct = Math.min(Math.max(((x - r.left) / r.width) * 100, 2), 98);
      applyPos();
    };
    const setPct = pct => {
      currentPct = Math.min(Math.max(pct, 2), 98);
      applyPos();
    };
    const applyPos = () => {
      handle.style.left = currentPct + '%';
      after.style.clipPath = 'inset(0 0 0 ' + currentPct + '%)';
      wrap.setAttribute('aria-valuenow', Math.round(currentPct));
    };

    applyPos();

    on(wrap, 'mousedown',  e => { dragging = true; setPos(e.clientX); e.preventDefault(); });
    on(wrap, 'touchstart', e => { dragging = true; setPos(e.touches[0].clientX); }, { passive: true });
    on(document, 'mousemove',  e => { if (dragging) setPos(e.clientX); });
    on(document, 'touchmove',  e => { if (dragging) setPos(e.touches[0].clientX); }, { passive: true });
    on(document, 'mouseup',   () => dragging = false);
    on(document, 'touchend',  () => dragging = false);

    on(wrap, 'keydown', e => {
      const step = e.shiftKey ? 10 : 2;
      if (e.key === 'ArrowLeft')  { e.preventDefault(); setPct(currentPct - step); }
      if (e.key === 'ArrowRight') { e.preventDefault(); setPct(currentPct + step); }
      if (e.key === 'Home')       { e.preventDefault(); setPct(2); }
      if (e.key === 'End')        { e.preventDefault(); setPct(98); }
    });
  });
})();

/* ── PROJECT CARD BEFORE/AFTER SLIDER ─────────────────── */
(function() {
  $$('[data-pc-ba]').forEach(wrap => {
    const handle = wrap.querySelector('.pc-ba-handle');
    const after  = wrap.querySelector('.pc-ba-after');
    if (!handle || !after) return;

    let pct = 50;
    let dragging = false;

    const applyPos = () => {
      handle.style.left = pct + '%';
      after.style.clipPath = 'inset(0 0 0 ' + pct + '%)';
    };
    const setFromX = x => {
      const r = wrap.getBoundingClientRect();
      pct = Math.min(Math.max(((x - r.left) / r.width) * 100, 2), 98);
      applyPos();
    };

    applyPos();

    on(wrap, 'mousedown',  e => { dragging = true; setFromX(e.clientX); e.preventDefault(); });
    on(wrap, 'touchstart', e => { dragging = true; setFromX(e.touches[0].clientX); }, { passive: true });
    on(document, 'mousemove',  e => { if (dragging) setFromX(e.clientX); });
    on(document, 'touchmove',  e => { if (dragging) setFromX(e.touches[0].clientX); }, { passive: true });
    on(document, 'mouseup',   () => dragging = false);
    on(document, 'touchend',  () => dragging = false);

    // Prevent card link from firing when user is dragging
    wrap.closest('a') && on(wrap.closest('a'), 'click', e => {
      if (wrap._dragged) { e.preventDefault(); wrap._dragged = false; }
    });
    on(wrap, 'mousedown', () => { wrap._dragged = false; });
    on(document, 'mousemove', () => { if (dragging) wrap._dragged = true; });
  });
})();

/* ── GALLERY FILTER ────────────────────────────────────── */
window.filterServices = function(btn, cat) {
  $$('[data-filter-btn]').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  $$('[data-cat]').forEach(el => {
    const show = cat === 'all' || el.dataset.cat === cat;
    el.style.display = show ? '' : 'none';
    if (show) el.style.animation = 'fadeIn .3s ease forwards';
  });
};
document.head.insertAdjacentHTML('beforeend', '<style>@keyframes fadeIn{from{opacity:0;transform:scale(.97)}to{opacity:1;transform:scale(1)}}</style>');

/* ── LIGHTBOX ──────────────────────────────────────────── */
window.openLightbox = function(src, title, sub) {
  let lb = $('#lightbox');
  if (!lb) {
    lb = document.createElement('div');
    lb.id = 'lightbox';
    lb.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.93);backdrop-filter:blur(16px);z-index:700;display:flex;align-items:center;justify-content:center;padding:24px;cursor:pointer';
    lb.innerHTML = '<div style="max-width:960px;width:100%;position:relative" onclick="event.stopPropagation()"><button onclick="closeLightbox()" style="position:absolute;top:-48px;right:0;width:40px;height:40px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.18);border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;font-size:18px">✕</button><img id="lb-img" style="width:100%;max-height:80vh;object-fit:contain;border-radius:12px;display:block"><div style="margin-top:14px;text-align:center"><div id="lb-title" style="font-family:var(--font-display);font-weight:600;font-size:1.1rem;color:#fff"></div><div id="lb-sub" style="font-size:13px;color:rgba(255,255,255,.4);margin-top:3px"></div></div></div>';
    lb.addEventListener('click', closeLightbox);
    document.body.appendChild(lb);
  }
  $('#lb-img').src = src; $('#lb-title').textContent = title||''; $('#lb-sub').textContent = sub||'';
  lb.style.display = 'flex';
  requestAnimationFrame(() => { lb.style.opacity='0'; lb.style.transition='opacity .22s'; requestAnimationFrame(() => lb.style.opacity='1'); });
  document.body.style.overflow = 'hidden';
};
window.closeLightbox = function() {
  const lb = $('#lightbox'); if (!lb) return;
  lb.style.opacity='0'; setTimeout(() => { lb.style.display='none'; lb.style.opacity='1'; }, 220);
  document.body.style.overflow = '';
};
document.addEventListener('keydown', e => e.key === 'Escape' && closeLightbox());

/* ── FORM AJAX ─────────────────────────────────────────── */
(function() {
  const form = $('#contact-form');
  if (!form) return;
  const upZone = form.querySelector('.upload-zone');
  const fileIn = form.querySelector('#file-input');
  const upStat = form.querySelector('#upload-status');
  on(upZone, 'click', () => fileIn?.click());
  on(fileIn, 'change', function() { if (upStat) upStat.textContent = this.files.length + ' fil(er) vald(a)'; });
  on(upZone, 'dragover', e => { e.preventDefault(); upZone.style.borderColor = 'var(--gold)'; });
  on(upZone, 'dragleave', () => upZone.style.borderColor = '');
  on(upZone, 'drop', e => { e.preventDefault(); if (fileIn) fileIn.files = e.dataTransfer.files; upZone.style.borderColor=''; if(upStat) upStat.textContent = e.dataTransfer.files.length + ' fil(er)'; });

  on(form, 'submit', async e => {
    e.preventDefault();
    const btn = form.querySelector('[type=submit]');
    const txt = btn?.querySelector('.btn-text');
    const spn = btn?.querySelector('.spinner');
    const err = form.querySelector('#form-error-box');
    const suc = form.querySelector('#form-success');
    if (err) err.style.display = 'none';
    if (spn) spn.style.display = 'inline-block';
    if (txt) txt.style.display = 'none';
    if (btn) btn.disabled = true;
    try {
      const res = await fetch(form.action, { method:'POST', body: new FormData(form) });
      const data = await res.json();
      if (data.success) { form.style.display='none'; if(suc) suc.style.display='block'; }
      else { if(err) { err.textContent = data.message||'Fel. Försök igen.'; err.style.display='block'; } }
    } catch { if(err) { err.textContent='Nätverksfel. Ring oss direkt.'; err.style.display='block'; } }
    finally { if(spn) spn.style.display='none'; if(txt) txt.style.display='inline'; if(btn) btn.disabled=false; }
  });
})();

/* ── SMOOTH ANCHORS ────────────────────────────────────── */
$$('a[href^="#"]').forEach(a => {
  on(a, 'click', e => {
    const t = document.getElementById(a.getAttribute('href').slice(1));
    if (!t) return; e.preventDefault();
    window.scrollTo({ top: t.getBoundingClientRect().top + window.scrollY - 88, behavior: 'smooth' });
  });
});

/* ── READING PROGRESS ──────────────────────────────────── */
(function() {
  const bar = $('#rp');
  if (!bar) return;
  on(window, 'scroll', () => {
    const h = document.documentElement;
    bar.style.width = (h.scrollTop / (h.scrollHeight - h.clientHeight) * 100) + '%';
  }, { passive: true });
})();

/* ── TABLE ROW CLICK ───────────────────────────────────── */
$$('tr[data-href]').forEach(tr => {
  on(tr, 'click', e => { if (!e.target.closest('a,button')) location.href = tr.dataset.href; });
  tr.style.cursor = 'pointer';
});