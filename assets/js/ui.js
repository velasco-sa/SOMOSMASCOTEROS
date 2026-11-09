/* =====================================================================
   SOMOS MASCOTEROS · Utilidades de interfaz compartidas
   Toasts, diálogos de confirmación, modales y helpers de Select2.
   ===================================================================== */
(function (w, d) {
  'use strict';

  const ICONS = {
    success: 'fa-solid fa-circle-check',
    error: 'fa-solid fa-circle-exclamation',
    warning: 'fa-solid fa-triangle-exclamation',
    info: 'fa-solid fa-circle-info',
    danger: 'fa-solid fa-trash-can',
    question: 'fa-solid fa-circle-question'
  };
  const TITLES = { success: '¡Listo!', error: 'Algo salió mal', warning: 'Atención', info: 'Información' };

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }

  function stack() {
    let el = d.getElementById('toastStack');
    if (!el) {
      el = d.createElement('div');
      el.id = 'toastStack';
      el.className = 'toast-stack';
      el.setAttribute('aria-live', 'polite');
      d.body.appendChild(el);
    }
    return el;
  }

  /** Muestra una alerta flotante con estilo. */
  function toast(type, message, title, ms) {
    type = TITLES[type] ? type : 'info';
    ms = ms || (type === 'error' ? 6500 : 4200);
    const t = d.createElement('div');
    t.className = 'toast toast--' + type;
    t.setAttribute('role', type === 'error' ? 'alert' : 'status');
    t.innerHTML = '<span class="toast__icon"><i class="' + ICONS[type] + '"></i></span>' +
      '<div><p class="toast__title">' + esc(title || TITLES[type]) + '</p>' +
      (message ? '<p class="toast__msg">' + esc(message) + '</p>' : '') + '</div>' +
      '<button class="toast__close" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>' +
      '<span class="toast__bar" style="animation-duration:' + ms + 'ms"></span>';
    stack().appendChild(t);
    let timer = setTimeout(close, ms);
    t.addEventListener('mouseenter', () => { clearTimeout(timer); t.querySelector('.toast__bar').style.animationPlayState = 'paused'; });
    t.addEventListener('mouseleave', () => { timer = setTimeout(close, 1800); t.querySelector('.toast__bar').style.animationPlayState = 'running'; });
    t.querySelector('.toast__close').addEventListener('click', close);
    function close() {
      clearTimeout(timer);
      t.classList.add('is-leaving');
      setTimeout(() => t.remove(), 360);
    }
    return t;
  }

  // ---------- Modales ----------
  let openCount = 0;
  let lastFocus = null;

  function openModal(el) {
    if (typeof el === 'string') el = d.getElementById(el);
    if (!el || el.classList.contains('is-open')) return;
    lastFocus = d.activeElement;
    el.classList.remove('is-closing');
    el.classList.add('is-open');
    el.setAttribute('aria-hidden', 'false');
    openCount++;
    d.body.classList.add('modal-open');
    setTimeout(() => {
      const f = el.querySelector('[autofocus], input:not([type=hidden]):not(.hp):not([readonly]), textarea, select, button.btn');
      if (f && w.matchMedia('(hover: hover)').matches) f.focus({ preventScroll: true });
    }, 120);
    el.dispatchEvent(new CustomEvent('modal:open'));
  }

  function closeModal(el) {
    if (typeof el === 'string') el = d.getElementById(el);
    if (!el || !el.classList.contains('is-open') || el.classList.contains('is-closing')) return;
    el.classList.add('is-closing');
    setTimeout(() => {
      el.classList.remove('is-open', 'is-closing');
      el.setAttribute('aria-hidden', 'true');
      openCount = Math.max(0, openCount - 1);
      if (!openCount) d.body.classList.remove('modal-open');
      el.dispatchEvent(new CustomEvent('modal:close'));
      if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
    }, 280);
  }

  d.addEventListener('click', e => {
    const opener = e.target.closest('[data-modal-open]');
    if (opener) { e.preventDefault(); openModal(opener.getAttribute('data-modal-open')); return; }
    const closer = e.target.closest('[data-modal-close]');
    if (closer) { e.preventDefault(); closeModal(closer.closest('.modal')); }
  });
  d.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      const all = d.querySelectorAll('.modal.is-open');
      if (all.length) closeModal(all[all.length - 1]);
    }
  });

  /** Diálogo de confirmación con estilo. Devuelve una promesa (true/false). */
  function confirmDialog(opts) {
    opts = Object.assign({ type: 'warning', title: '¿Estás seguro?', text: '', confirmText: 'Sí, continuar', cancelText: 'Cancelar', showCancel: true }, opts || {});
    return new Promise(resolve => {
      const m = d.createElement('div');
      m.className = 'modal modal--sm dialog';
      m.setAttribute('role', 'alertdialog');
      m.setAttribute('aria-modal', 'true');
      const icon = ICONS[opts.type] || ICONS.question;
      const btnCls = opts.type === 'danger' ? 'btn--danger' : 'btn--primary';
      m.innerHTML = '<div class="modal__backdrop"></div><div class="modal__dialog">' +
        '<div class="dialog__icon dialog__icon--' + esc(opts.type) + '"><i class="' + icon + '"></i></div>' +
        '<h3>' + esc(opts.title) + '</h3>' + (opts.text ? '<p>' + esc(opts.text) + '</p>' : '') +
        '<div class="modal__foot">' +
        (opts.showCancel ? '<button type="button" class="btn btn--ghost" data-act="no">' + esc(opts.cancelText) + '</button>' : '') +
        '<button type="button" class="btn ' + btnCls + '" data-act="yes">' + esc(opts.confirmText) + '</button></div></div>';
      d.body.appendChild(m);
      requestAnimationFrame(() => openModal(m));
      let done = false;
      function finish(v) {
        if (done) return; done = true;
        closeModal(m);
        setTimeout(() => m.remove(), 400);
        resolve(v);
      }
      m.addEventListener('click', e => {
        const b = e.target.closest('[data-act]');
        if (b) finish(b.dataset.act === 'yes');
        else if (e.target.classList.contains('modal__backdrop')) finish(false);
      });
      m.addEventListener('modal:close', () => finish(false));
    });
  }

  function alertDialog(type, title, text) {
    return confirmDialog({ type, title, text, showCancel: false, confirmText: 'Entendido' });
  }

  // ---------- Select2 ----------
  function s2Format(opt) {
    if (!opt.id || !opt.element) return opt.text;
    const icon = opt.element.getAttribute('data-icon');
    const color = opt.element.getAttribute('data-color');
    if (!icon && !color) return opt.text;
    const span = d.createElement('span');
    span.style.display = 'inline-flex'; span.style.alignItems = 'center'; span.style.gap = '.6rem'; span.style.minWidth = '0';
    if (color && /^#[0-9a-f]{6}$/i.test(color)) {
      span.innerHTML = '<span class="s2-swatch" style="background:' + color + '"></span>';
    } else if (icon && /^[a-z0-9\- ]+$/.test(icon)) {
      span.innerHTML = '<span class="s2-ico"><i class="' + icon + '"></i></span>';
    }
    const txt = d.createElement('span');
    txt.textContent = opt.text;
    txt.style.overflow = 'hidden'; txt.style.textOverflow = 'ellipsis';
    span.appendChild(txt);
    return span;
  }

  function initSelect2(scope, extra) {
    if (!w.jQuery || !w.jQuery.fn.select2) return;
    const $ = w.jQuery;
    $(scope || d).find('select.select2').each(function () {
      const $s = $(this);
      if ($s.hasClass('select2-hidden-accessible')) return;
      const modal = $s.closest('.modal__dialog');
      const count = $s.find('option').length;
      $s.select2(Object.assign({
        width: '100%',
        placeholder: $s.data('placeholder') || 'Selecciona una opción',
        allowClear: $s.data('allow-clear') === true || $s.data('allow-clear') === 'true',
        minimumResultsForSearch: count > 8 ? 0 : Infinity,
        dropdownParent: modal.length ? modal : $(d.body),
        templateResult: s2Format,
        templateSelection: s2Format,
        language: {
          noResults: () => 'Sin resultados',
          searching: () => 'Buscando…',
          removeAllItems: () => 'Quitar todo'
        }
      }, extra || {}));
    });
  }

  /** POST con CSRF que devuelve JSON. */
  async function post(url, data) {
    const body = data instanceof FormData ? data : (() => { const f = new FormData(); Object.entries(data || {}).forEach(([k, v]) => f.append(k, v == null ? '' : v)); return f; })();
    let res, json;
    try {
      res = await fetch(url, { method: 'POST', body, headers: { 'X-CSRF-Token': (w.SM && w.SM.csrf) || '', 'Accept': 'application/json' }, credentials: 'same-origin' });
      json = await res.json();
    } catch (e) {
      json = { ok: false, message: 'No se pudo conectar con el servidor. Revisa tu conexión.' };
    }
    if (res && !res.ok && json.ok === undefined) json.ok = false;
    return json;
  }

  function setLoading(btn, on) {
    if (!btn) return;
    if (on) {
      btn.dataset.html = btn.innerHTML;
      btn.classList.add('is-loading');
      btn.innerHTML = '<i class="fa-solid fa-circle-notch"></i> <span>Procesando…</span>';
    } else {
      btn.classList.remove('is-loading');
      if (btn.dataset.html) btn.innerHTML = btn.dataset.html;
    }
  }

  function markInvalid(form, field) {
    if (!form || !field) return;
    const el = form.querySelector('[name="' + field + '"]');
    if (!el) return;
    const f = el.closest('.field');
    (f || el).classList.add('is-invalid');
    el.focus({ preventScroll: false });
  }
  d.addEventListener('input', e => { const f = e.target.closest('.field.is-invalid'); if (f) f.classList.remove('is-invalid'); e.target.classList && e.target.classList.remove('is-invalid'); });
  d.addEventListener('change', e => { const f = e.target.closest && e.target.closest('.field.is-invalid'); if (f) f.classList.remove('is-invalid'); });

  // Contadores +/-
  d.addEventListener('click', e => {
    const b = e.target.closest('[data-stepper] [data-step]');
    if (!b) return;
    const input = b.parentElement.querySelector('input');
    const min = +input.min || 0, max = +input.max || 999;
    input.value = Math.min(max, Math.max(min, (+input.value || 0) + (+b.dataset.step)));
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });

  w.UI = { toast, confirm: confirmDialog, alert: alertDialog, openModal, closeModal, initSelect2, s2Format, post, setLoading, markInvalid, esc };
})(window, document);
