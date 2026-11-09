/* =====================================================================
   SOMOS MASCOTEROS · Interacciones del sitio público
   ===================================================================== */
(function (w, d) {
  'use strict';
  const $$ = (s, c) => Array.from((c || d).querySelectorAll(s));
  const $1 = (s, c) => (c || d).querySelector(s);

  // ---------- Pantalla de carga ----------
  function hidePreloader() { const p = $1('#preloader'); if (p) p.classList.add('is-done'); }
  w.addEventListener('load', () => setTimeout(hidePreloader, 250));
  setTimeout(hidePreloader, 3500);

  // ---------- Navegación ----------
  const nav = $1('#nav');
  const burger = $1('#burger');
  const progress = $1('#scrollProgress');
  function onScroll() {
    const y = w.scrollY;
    if (nav) nav.classList.toggle('is-scrolled', y > 40);
    if (progress) {
      const h = d.documentElement.scrollHeight - w.innerHeight;
      progress.style.transform = 'scaleX(' + (h > 0 ? y / h : 0) + ')';
    }
  }
  w.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  function toggleMenu(force) {
    const open = typeof force === 'boolean' ? force : !d.body.classList.contains('menu-open');
    d.body.classList.toggle('menu-open', open);
    if (burger) burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    d.documentElement.style.overflow = open ? 'hidden' : '';
  }
  if (burger) burger.addEventListener('click', () => toggleMenu());
  $$('.nav__links a').forEach(a => a.addEventListener('click', () => toggleMenu(false)));
  w.addEventListener('resize', () => { if (w.innerWidth > 1320) toggleMenu(false); });

  // Resaltar la sección activa del menú
  const anchored = $$('.nav__link[data-anchor]');
  if (anchored.length && 'IntersectionObserver' in w) {
    const map = {};
    anchored.forEach(a => { map[a.dataset.anchor] = a; });
    const io = new IntersectionObserver(entries => {
      entries.forEach(en => {
        if (en.isIntersecting && map[en.target.id]) {
          anchored.forEach(a => a.classList.remove('is-active'));
          map[en.target.id].classList.add('is-active');
        }
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    Object.keys(map).forEach(id => { const s = d.getElementById(id); if (s) io.observe(s); });
  }

  // Botón "bajar" de la portada
  const scrollNext = $1('[data-scroll-next]');
  if (scrollNext) scrollNext.addEventListener('click', e => {
    e.preventDefault();
    const hero = scrollNext.closest('section');
    const next = hero && hero.nextElementSibling;
    if (next) next.scrollIntoView({ behavior: 'smooth' });
  });

  // ---------- Revelado al hacer scroll ----------
  const reveals = $$('[data-reveal]');
  if ('IntersectionObserver' in w) {
    const ro = new IntersectionObserver(entries => {
      entries.forEach(en => { if (en.isIntersecting) { en.target.classList.add('is-visible'); ro.unobserve(en.target); } });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    reveals.forEach(el => ro.observe(el));
  } else {
    reveals.forEach(el => el.classList.add('is-visible'));
  }

  // ---------- Contadores animados ----------
  $$('[data-count]').forEach(el => {
    const raw = el.dataset.count || '';
    const m = raw.match(/^(\D*)(\d[\d,.]*)(.*)$/);
    if (!m) return;
    const target = parseFloat(m[2].replace(/,/g, ''));
    if (!isFinite(target) || target === 0) return;
    const io = new IntersectionObserver(en => {
      if (!en[0].isIntersecting) return;
      io.disconnect();
      const start = performance.now(), dur = 1600;
      (function tick(now) {
        const p = Math.min(1, (now - start) / dur), eased = 1 - Math.pow(1 - p, 3);
        el.textContent = m[1] + Math.round(target * eased).toLocaleString('es') + m[3];
        if (p < 1) requestAnimationFrame(tick); else el.textContent = raw;
      })(start);
    });
    io.observe(el);
  });

  // ---------- Brillo que sigue al cursor en servicios ----------
  $$('.service').forEach(card => card.addEventListener('pointermove', e => {
    const r = card.getBoundingClientRect();
    card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
    card.style.setProperty('--my', (e.clientY - r.top) + 'px');
  }));

  // ---------- Galería: filtros y visor ----------
  $$('.filter').forEach(btn => btn.addEventListener('click', () => {
    $$('.filter').forEach(b => b.classList.remove('is-active'));
    btn.classList.add('is-active');
    const f = btn.dataset.filter;
    $$('.masonry__item').forEach(it => it.classList.toggle('is-hidden', f !== 'all' && it.dataset.cat !== f));
  }));

  const lb = $1('#lightbox');
  let lbList = [], lbIdx = 0;
  function lbShow(i) {
    if (!lbList.length) return;
    lbIdx = (i + lbList.length) % lbList.length;
    const b = lbList[lbIdx], img = $1('img', lb);
    img.src = b.dataset.full;
    img.alt = b.dataset.caption || '';
    $1('figcaption', lb).textContent = b.dataset.caption || '';
  }
  function lbOpen(btn) {
    lbList = $$('.masonry__item:not(.is-hidden) .masonry__btn');
    lbShow(lbList.indexOf(btn));
    lb.classList.add('is-open'); lb.setAttribute('aria-hidden', 'false');
    d.body.classList.add('modal-open');
  }
  function lbClose() { lb.classList.remove('is-open'); lb.setAttribute('aria-hidden', 'true'); d.body.classList.remove('modal-open'); }
  if (lb) {
    $$('.masonry__btn').forEach(b => b.addEventListener('click', () => lbOpen(b)));
    lb.addEventListener('click', e => {
      const a = e.target.closest('[data-lb]');
      if (a) { a.dataset.lb === 'close' ? lbClose() : lbShow(lbIdx + (a.dataset.lb === 'next' ? 1 : -1)); }
      else if (e.target === lb) lbClose();
    });
    d.addEventListener('keydown', e => {
      if (!lb.classList.contains('is-open')) return;
      if (e.key === 'Escape') lbClose();
      if (e.key === 'ArrowRight') lbShow(lbIdx + 1);
      if (e.key === 'ArrowLeft') lbShow(lbIdx - 1);
    });
    let sx = null;
    lb.addEventListener('touchstart', e => { sx = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', e => { if (sx === null) return; const dx = e.changedTouches[0].clientX - sx; if (Math.abs(dx) > 50) lbShow(lbIdx + (dx < 0 ? 1 : -1)); sx = null; });
  }

  // ---------- Carrusel de testimonios ----------
  $$('[data-slider]').forEach(sl => {
    const track = $1('.testimonials__track', sl), dots = $1('.slider-dots', sl);
    if (!track) return;
    const cards = $$('.quote', track);
    function perView() { const c = cards[0]; return c ? Math.max(1, Math.round(track.clientWidth / c.clientWidth)) : 1; }
    function pages() { return Math.max(1, cards.length - perView() + 1); }
    function renderDots() {
      if (!dots) return;
      dots.innerHTML = '';
      for (let i = 0; i < pages(); i++) {
        const b = d.createElement('button'); b.type = 'button'; b.setAttribute('aria-label', 'Ir a ' + (i + 1));
        b.addEventListener('click', () => go(i)); dots.appendChild(b);
      }
      sync();
    }
    function current() { const c = cards[0]; return c ? Math.round(track.scrollLeft / (c.clientWidth + 19)) : 0; }
    function go(i) { const c = cards[Math.max(0, Math.min(cards.length - 1, i))]; if (c) track.scrollTo({ left: c.offsetLeft - track.offsetLeft, behavior: 'smooth' }); }
    function sync() { if (!dots) return; const cur = current(); $$('button', dots).forEach((b, i) => b.classList.toggle('is-active', i === Math.min(cur, pages() - 1))); }
    $$('[data-slide]', sl).forEach(b => b.addEventListener('click', () => {
      let n = current() + (+b.dataset.slide);
      if (n >= pages()) n = 0; if (n < 0) n = pages() - 1;
      go(n);
    }));
    track.addEventListener('scroll', () => w.requestAnimationFrame(sync), { passive: true });
    w.addEventListener('resize', renderDots);
    renderDots();
    let auto = setInterval(() => { if (!d.hidden) { let n = current() + 1; if (n >= pages()) n = 0; go(n); } }, 6500);
    sl.addEventListener('pointerenter', () => clearInterval(auto));
  });

  // ---------- Select2 ----------
  if (w.UI) UI.initSelect2(d);

  // ---------- Simulador de disponibilidad ----------
  const sim = $1('.sim');
  const simForm = $1('#simForm');
  let lastQuery = null;
  if (sim && simForm && w.flatpickr) {
    const minAdvance = +sim.dataset.minAdvance || 0;
    const maxNights = +sim.dataset.maxNights || 60;
    const minDate = new Date(); minDate.setHours(0, 0, 0, 0); minDate.setDate(minDate.getDate() + minAdvance);
    const fp = flatpickr('#simDates', {
      mode: 'range', locale: 'es', minDate, dateFormat: 'Y-m-d', altInput: true, altFormat: 'j M Y', altInputClass: 'input',
      showMonths: w.innerWidth > 760 ? 2 : 1, disableMobile: true,
      onChange(sel, str, inst) {
        if (sel.length === 2) {
          const n = Math.round((sel[1] - sel[0]) / 864e5);
          if (n < 1) { UI.toast('warning', 'La salida debe ser al menos un día después de la llegada.'); inst.clear(); }
          else if (n > maxNights) { UI.toast('warning', 'La estancia máxima es de ' + maxNights + ' noches.'); inst.clear(); }
        }
      }
    });
    const fmtDay = new Intl.DateTimeFormat('es', { weekday: 'short' });
    const fmtMonth = new Intl.DateTimeFormat('es', { month: 'short' });
    const fmtLong = new Intl.DateTimeFormat('es', { day: 'numeric', month: 'long', year: 'numeric' });
    const ymd = dt => dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(dt.getDate()).padStart(2, '0');

    simForm.addEventListener('submit', async e => {
      e.preventDefault();
      const pet = $1('#simPet').value;
      const sel = fp.selectedDates;
      if (!pet) { UI.toast('warning', 'Selecciona el tipo de mascota.'); UI.markInvalid(simForm, 'pet_type_id'); return; }
      if (sel.length < 2) { UI.toast('warning', 'Selecciona la fecha de llegada y la de salida.'); fp.open(); return; }
      const btn = $1('.sim__submit', simForm);
      UI.setLoading(btn, true);
      const q = { pet_type_id: pet, check_in: ymd(sel[0]), check_out: ymd(sel[1]), pets_count: $1('#simCount').value || 1 };
      const r = await UI.post('api/availability.php', q);
      UI.setLoading(btn, false);
      if (!r.ok) { UI.toast('error', r.message); return; }
      lastQuery = Object.assign(q, { petName: $1('#simPet option:checked').textContent.trim(), nights: r.nights });
      renderResult(r);
    });

    function renderResult(r) {
      const box = $1('#simResult'), status = $1('#simStatus'), days = $1('#simDays'), cta = $1('#simCta');
      box.hidden = false;
      status.className = 'sim__status ' + (r.available ? 'sim__status--ok' : 'sim__status--full');
      status.innerHTML = '<i class="fa-solid ' + (r.available ? 'fa-circle-check' : 'fa-circle-xmark') + '"></i><div><strong>' +
        (r.available ? 'Disponible' : 'Sin disponibilidad completa') + ' · ' + r.nights + (r.nights === 1 ? ' noche' : ' noches') + '</strong><p>' +
        UI.esc(r.message || (r.available ? sim.dataset.okMsg : sim.dataset.fullMsg)) + '</p></div>';
      days.innerHTML = r.days.map((dd, i) => {
        const dt = new Date(dd.date + 'T12:00:00');
        let cls = 'day--ok', label;
        if (dd.closed) { cls = 'day--full'; label = 'Cerrado'; }
        else if (!dd.night) { cls = dd.ok ? 'day--out' : 'day--full'; label = 'Salida'; }
        else if (!dd.ok) { cls = 'day--full'; label = dd.free === 0 ? 'Lleno' : dd.free + ' libres'; }
        else { label = dd.free + (dd.free === 1 ? ' libre' : ' libres'); if (dd.capacity && dd.free / dd.capacity <= .25) cls = 'day--low'; }
        return '<div class="day ' + cls + '" style="animation-delay:' + Math.min(i * 25, 600) + 'ms" title="' + UI.esc(dd.closed || '') + '"><small>' + fmtDay.format(dt) +
          '</small><b>' + dt.getDate() + '</b><small>' + fmtMonth.format(dt) + '</small><em>' + label + '</em></div>';
      }).join('');
      if (cta) cta.hidden = !r.available;
      box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      const sum = $1('#bookingSummary');
      if (sum && lastQuery) {
        sum.textContent = lastQuery.petName + ' · ' + fmtLong.format(new Date(lastQuery.check_in + 'T12:00:00')) + ' → ' +
          fmtLong.format(new Date(lastQuery.check_out + 'T12:00:00')) + ' (' + lastQuery.nights + (lastQuery.nights === 1 ? ' noche' : ' noches') + ')';
      }
    }
    // Si cambian los datos, ocultar el resultado anterior
    $$('#simPet, #simCount').forEach(el => el.addEventListener('change', () => { const c = $1('#simCta'); if (c) c.hidden = true; }));
    if (w.jQuery) w.jQuery('#simPet').on('change', () => { const c = $1('#simCta'); if (c) c.hidden = true; });
    fp.config.onChange.push(() => { const c = $1('#simCta'); if (c) c.hidden = true; });
  }

  const bookingForm = $1('#bookingForm');
  if (bookingForm) bookingForm.addEventListener('submit', async e => {
    e.preventDefault();
    if (!lastQuery) { UI.toast('warning', 'Primero consulta la disponibilidad.'); return; }
    if (!validate(bookingForm)) return;
    const fd = new FormData(bookingForm);
    ['pet_type_id', 'check_in', 'check_out', 'pets_count'].forEach(k => fd.append(k, lastQuery[k]));
    const btn = $1('button[type=submit]', bookingForm);
    UI.setLoading(btn, true);
    const r = await UI.post('api/booking.php', fd);
    UI.setLoading(btn, false);
    if (!r.ok) { UI.toast('error', r.message); UI.markInvalid(bookingForm, r.field); return; }
    UI.closeModal('bookingModal');
    bookingForm.reset();
    const c = $1('#simCta'); if (c) c.hidden = true;
    UI.alert('success', '¡Solicitud enviada!', r.message);
  });

  // ---------- Formulario de contacto ----------
  const contactForm = $1('#contactForm');
  if (contactForm) contactForm.addEventListener('submit', async e => {
    e.preventDefault();
    if (!validate(contactForm)) return;
    const btn = $1('button[type=submit]', contactForm);
    UI.setLoading(btn, true);
    const r = await UI.post('api/contact.php', new FormData(contactForm));
    UI.setLoading(btn, false);
    if (!r.ok) { UI.toast('error', r.message); UI.markInvalid(contactForm, r.field); return; }
    contactForm.reset();
    UI.toast('success', r.message, '¡Mensaje enviado!');
  });

  /** Validación visual sin las burbujas nativas del navegador. */
  function validate(form) {
    let first = null;
    $$('[required]', form).forEach(el => {
      const v = (el.value || '').trim();
      const bad = !v || (el.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v));
      const f = el.closest('.field');
      if (bad) { (f || el).classList.add('is-invalid'); if (!first) first = el; }
    });
    if (first) {
      first.focus();
      const lbl = first.closest('.field') && $1('.field__label', first.closest('.field'));
      UI.toast('warning', 'Revisa el campo: ' + (lbl ? lbl.textContent.trim() : 'requerido') + '.', 'Faltan datos');
      return false;
    }
    return true;
  }
})(window, document);
