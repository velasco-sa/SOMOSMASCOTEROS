/* =====================================================================
   SOMOS MASCOTEROS · Panel de administración
   ===================================================================== */
(function (w, d, $) {
  'use strict';
  const SM = w.SM || {};
  const $1 = (s, c) => (c || d).querySelector(s);
  const $$ = (s, c) => Array.from((c || d).querySelectorAll(s));
  const esc = UI.esc;
  const API = 'api.php';
  const api = (action, data) => {
    const fd = data instanceof FormData ? data : new FormData();
    if (!(data instanceof FormData)) Object.entries(data || {}).forEach(([k, v]) => appendDeep(fd, k, v));
    fd.append('action', action);
    return UI.post(API, fd).then(r => {
      if (r && r.login) { UI.alert('warning', 'Sesión terminada', r.message).then(() => location.href = 'index.php'); }
      return r;
    });
  };
  function appendDeep(fd, key, val) {
    if (val && typeof val === 'object' && !(val instanceof File)) Object.entries(val).forEach(([k, v]) => appendDeep(fd, key + '[' + k + ']', v));
    else fd.append(key, val == null ? '' : val);
  }
  const MONTHS = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
  function fmtDate(v, withTime) {
    if (!v) return '';
    const m = String(v).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
    if (!m) return esc(v);
    return (+m[3]) + ' ' + MONTHS[+m[2] - 1] + ' ' + m[1] + (withTime && m[4] ? ' · ' + m[4] + ':' + m[5] : '');
  }
  const url = p => !p ? '' : (/^(https?:)?\/\//.test(p) ? p : (SM.base || '../') + p);
  const debounce = (fn, ms) => { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; };

  // Catálogo de íconos para el selector
  const ICONS = ['fa-solid fa-paw', 'fa-solid fa-dog', 'fa-solid fa-cat', 'fa-solid fa-dove', 'fa-solid fa-fish', 'fa-solid fa-horse', 'fa-solid fa-kiwi-bird', 'fa-solid fa-frog', 'fa-solid fa-otter', 'fa-solid fa-bone',
    'fa-solid fa-shield-dog', 'fa-solid fa-shield-cat', 'fa-solid fa-heart', 'fa-regular fa-heart', 'fa-solid fa-hand-holding-heart', 'fa-solid fa-house', 'fa-solid fa-house-chimney', 'fa-solid fa-house-chimney-user', 'fa-solid fa-bed', 'fa-solid fa-moon',
    'fa-solid fa-sun', 'fa-solid fa-star', 'fa-solid fa-award', 'fa-solid fa-medal', 'fa-solid fa-trophy', 'fa-solid fa-gem', 'fa-solid fa-crown', 'fa-solid fa-bowl-food', 'fa-solid fa-bowl-rice', 'fa-solid fa-utensils',
    'fa-solid fa-pills', 'fa-solid fa-syringe', 'fa-solid fa-stethoscope', 'fa-solid fa-kit-medical', 'fa-solid fa-user-nurse', 'fa-solid fa-user-doctor', 'fa-solid fa-soap', 'fa-solid fa-shower', 'fa-solid fa-bath', 'fa-solid fa-scissors',
    'fa-solid fa-spray-can-sparkles', 'fa-solid fa-broom', 'fa-solid fa-camera', 'fa-solid fa-video', 'fa-solid fa-mobile-screen', 'fa-solid fa-futbol', 'fa-solid fa-baseball', 'fa-solid fa-person-walking', 'fa-solid fa-person-running', 'fa-solid fa-tree',
    'fa-solid fa-seedling', 'fa-solid fa-leaf', 'fa-solid fa-mountain-sun', 'fa-solid fa-umbrella-beach', 'fa-solid fa-graduation-cap', 'fa-solid fa-book-open', 'fa-solid fa-lightbulb', 'fa-solid fa-rocket', 'fa-solid fa-bullseye', 'fa-solid fa-eye',
    'fa-solid fa-van-shuttle', 'fa-solid fa-car', 'fa-solid fa-truck', 'fa-solid fa-store', 'fa-solid fa-shop', 'fa-solid fa-people-group', 'fa-solid fa-users', 'fa-solid fa-user', 'fa-solid fa-user-tie', 'fa-solid fa-handshake',
    'fa-solid fa-calendar', 'fa-solid fa-calendar-check', 'fa-solid fa-calendar-days', 'fa-solid fa-clock', 'fa-regular fa-clock', 'fa-solid fa-hourglass-half', 'fa-solid fa-bell', 'fa-solid fa-bell-concierge', 'fa-solid fa-gift', 'fa-solid fa-tag',
    'fa-solid fa-dollar-sign', 'fa-solid fa-money-bill-wave', 'fa-solid fa-credit-card', 'fa-solid fa-percent', 'fa-solid fa-phone', 'fa-solid fa-envelope', 'fa-solid fa-location-dot', 'fa-solid fa-map-location-dot', 'fa-solid fa-signs-post', 'fa-solid fa-comments',
    'fa-solid fa-quote-left', 'fa-solid fa-circle-question', 'fa-solid fa-circle-info', 'fa-solid fa-clipboard-check', 'fa-solid fa-list-check', 'fa-solid fa-check', 'fa-solid fa-thumbs-up', 'fa-solid fa-face-smile', 'fa-solid fa-face-grin-hearts', 'fa-solid fa-music',
    'fa-solid fa-temperature-half', 'fa-solid fa-fan', 'fa-solid fa-snowflake', 'fa-solid fa-wifi', 'fa-solid fa-lock', 'fa-solid fa-shield-halved', 'fa-solid fa-key', 'fa-solid fa-arrow-right', 'fa-solid fa-paper-plane', 'fa-solid fa-link',
    'fa-solid fa-image', 'fa-solid fa-images', 'fa-solid fa-palette', 'fa-solid fa-wand-magic-sparkles', 'fa-solid fa-store-slash', 'fa-solid fa-file-lines', 'fa-solid fa-layer-group', 'fa-solid fa-gauge-high', 'fa-solid fa-sliders', 'fa-solid fa-gear',
    'fa-solid fa-gears', 'fa-solid fa-puzzle-piece', 'fa-solid fa-folder-tree', 'fa-solid fa-clock-rotate-left', 'fa-solid fa-user-shield', 'fa-solid fa-user-group', 'fa-solid fa-address-book', 'fa-solid fa-id-card', 'fa-solid fa-id-badge', 'fa-solid fa-share-nodes',
    'fa-solid fa-hashtag', 'fa-solid fa-flag', 'fa-solid fa-calendar-xmark', 'fa-solid fa-envelope-open-text', 'fa-solid fa-photo-film', 'fa-solid fa-tags', 'fa-solid fa-table-cells-large', 'fa-solid fa-shapes', 'fa-solid fa-shoe-prints', 'fa-solid fa-magnifying-glass',
    'fa-solid fa-timeline', 'fa-solid fa-align-left', 'fa-solid fa-bullhorn', 'fa-solid fa-images', 'fa-brands fa-whatsapp', 'fa-brands fa-instagram', 'fa-brands fa-facebook-f', 'fa-brands fa-tiktok', 'fa-brands fa-youtube', 'fa-brands fa-x-twitter',
    'fa-brands fa-linkedin-in', 'fa-brands fa-pinterest-p', 'fa-brands fa-telegram', 'fa-brands fa-threads', 'fa-brands fa-google', 'fa-brands fa-spotify'];

  /* ------------------------------------------------------------------ Común */
  // Menú lateral
  const toggle = $1('#sidebarToggle');
  function sidebar(open) { d.body.classList.toggle('sidebar-open', open); }
  if (toggle) toggle.addEventListener('click', () => sidebar(!d.body.classList.contains('sidebar-open')));
  $$('[data-sidebar-close]').forEach(b => b.addEventListener('click', () => sidebar(false)));
  const activeLink = $1('.sidebar__link.is-active');
  if (activeLink) activeLink.scrollIntoView({ block: 'center' });

  // Buscador del menú
  const ms = $1('#menuSearch');
  if (ms) ms.addEventListener('input', () => {
    const q = ms.value.trim().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    $$('.sidebar__group').forEach(g => {
      let any = false;
      $$('.sidebar__link', g).forEach(a => {
        const ok = !q || a.textContent.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').includes(q);
        a.hidden = !ok; any = any || ok;
      });
      g.hidden = !any;
    });
  });

  // Menú de usuario
  const um = $1('#userMenu');
  if (um) {
    const btn = $1('.usermenu__btn', um);
    btn.addEventListener('click', e => { e.stopPropagation(); um.classList.toggle('is-open'); btn.setAttribute('aria-expanded', um.classList.contains('is-open')); });
    d.addEventListener('click', e => { if (!um.contains(e.target)) um.classList.remove('is-open'); });
  }
  $$('[data-logout]').forEach(a => a.addEventListener('click', async e => {
    e.preventDefault();
    if (await UI.confirm({ type: 'question', title: '¿Cerrar sesión?', text: 'Tendrás que volver a ingresar tus datos para entrar al panel.', confirmText: 'Cerrar sesión' })) location.href = a.href;
  }));

  // Mostrar / ocultar contraseña
  d.addEventListener('click', e => {
    const b = e.target.closest('[data-toggle-pass]');
    if (!b) return;
    const i = d.getElementById(b.dataset.togglePass);
    i.type = i.type === 'password' ? 'text' : 'password';
    b.innerHTML = '<i class="fa-regular ' + (i.type === 'password' ? 'fa-eye' : 'fa-eye-slash') + '"></i>';
  });

  // Copiar al portapapeles
  d.addEventListener('click', e => {
    const b = e.target.closest('[data-copy]');
    if (!b) return;
    const i = b.parentElement.querySelector('input');
    navigator.clipboard && navigator.clipboard.writeText(i.value).then(() => UI.toast('success', 'Copiado al portapapeles.'));
  });

  // Contadores
  $$('[data-count]').forEach(el => {
    const t = +el.dataset.count; if (!t) return;
    const s = performance.now();
    (function tick(n) { const p = Math.min(1, (n - s) / 900); el.textContent = Math.round(t * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(tick); })(s);
  });

  // Fortaleza de contraseña
  function bindStrength(input) {
    const box = $1('[data-strength-for="' + input.id + '"]');
    if (!box) return;
    input.addEventListener('input', () => {
      const v = input.value; let s = 0;
      if (v.length >= 8) s++; if (v.length >= 12) s++; if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++; if (/\d/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
      const lv = !v ? 0 : s <= 2 ? 1 : s <= 3 ? 2 : 3;
      box.dataset.level = lv;
      $1('small', box).textContent = ['', 'Débil', 'Aceptable', 'Fuerte'][lv];
    });
  }

  // Login: validación con alertas propias
  if (SM.page === 'login') {
    const f = $1('#loginForm');
    f.addEventListener('submit', e => {
      const em = $1('#email', f), pw = $1('#password', f);
      if (!em.value.trim() || !pw.value) {
        e.preventDefault();
        UI.toast('warning', 'Escribe tu correo y tu contraseña.', 'Faltan datos');
        (!em.value.trim() ? em : pw).closest('.field').classList.add('is-invalid');
        return;
      }
      UI.setLoading($1('button[type=submit]', f), true);
    });
  }

  /* ------------------------------------------------------------------ Selector de imágenes */
  const Media = (function () {
    let pickCb = null;
    function init(root) {
      const grid = $1('[data-media-grid]', root), pager = $1('[data-media-pager]', root), search = $1('[data-media-search]', root);
      const file = $1('[data-media-file]', root), drop = $1('[data-media-drop]', root);
      const manage = root.dataset.mode === 'manage';
      const st = { page: 1, q: '', perms: {} };
      async function load() {
        grid.innerHTML = Array.from({ length: 8 }, () => '<div class="media-card skeleton" style="aspect-ratio:1"></div>').join('');
        const r = await api('media_list', { page: st.page, q: st.q });
        if (!r.ok) { grid.innerHTML = ''; UI.toast('error', r.message); return; }
        st.perms = r.perms;
        const upBtn = $1('[data-media-upload-btn]', root); if (upBtn) upBtn.hidden = !r.perms.create;
        if (drop) drop.hidden = !r.perms.create;
        grid.innerHTML = r.rows.length ? r.rows.map(m => '<button type="button" class="media-card" data-id="' + m.id + '">' +
          '<img src="' + esc(url(m.file_path)) + '" alt="' + esc(m.alt_text || '') + '" loading="lazy">' +
          '<span class="media-card__name">' + esc(m.alt_text || m.original_name) + '</span>' +
          '<span class="media-card__check"><i class="fa-solid ' + (manage ? 'fa-pen' : 'fa-check') + '"></i></span></button>').join('')
          : '<div class="empty-panel empty-panel--sm"><span class="empty-panel__icon"><i class="fa-regular fa-images"></i></span><p>No hay imágenes' + (st.q ? ' con esa búsqueda' : ' todavía') + '.</p></div>';
        grid._rows = r.rows;
        renderPager(pager, r.page, r.pages, r.total, p => { st.page = p; load(); }, 'imágenes');
      }
      grid.addEventListener('click', e => {
        const c = e.target.closest('.media-card[data-id]');
        if (!c) return;
        const m = grid._rows.find(x => String(x.id) === c.dataset.id);
        if (manage) openEdit(m, load, st.perms);
        else if (pickCb) { const cb = pickCb; pickCb = null; UI.closeModal('mediaModal'); cb(m); }
      });
      if (search) search.addEventListener('input', debounce(() => { st.q = search.value.trim(); st.page = 1; load(); }, 350));
      async function upload(files) {
        files = Array.from(files || []).filter(f => /^image\//.test(f.type));
        if (!files.length) { UI.toast('warning', 'Selecciona imágenes JPG, PNG, WEBP o GIF.'); return; }
        const fd = new FormData(); files.forEach(f => fd.append('files[]', f));
        const t = UI.toast('info', 'Subiendo ' + files.length + (files.length === 1 ? ' imagen…' : ' imágenes…'), 'Un momento', 60000);
        const r = await api('media_upload', fd);
        t.querySelector('.toast__close').click();
        if (!r.ok) { UI.toast('error', r.message); return; }
        UI.toast('success', r.message);
        st.page = 1; st.q = ''; if (search) search.value = '';
        await load();
        if (!manage && pickCb && r.items.length === 1) { const cb = pickCb; pickCb = null; UI.closeModal('mediaModal'); cb(r.items[0]); }
      }
      if (file) file.addEventListener('change', () => { upload(file.files); file.value = ''; });
      if (drop) {
        drop.addEventListener('click', () => file && file.click());
        ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('is-drag'); }));
        ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('is-drag'); }));
        drop.addEventListener('drop', e => upload(e.dataTransfer.files));
      }
      root._load = load;
      if (manage) load();
    }
    function openEdit(m, reload, perms) {
      const md = $1('#mediaEdit');
      $1('[data-me-img]', md).src = url(m.file_path);
      $1('[data-me-name]', md).textContent = m.original_name;
      $1('#meAlt', md).value = m.alt_text || '';
      $1('[data-me-url]', md).value = new URL(url(m.file_path), location.href).href;
      const kb = m.size_bytes ? (m.size_bytes > 1048576 ? (m.size_bytes / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(m.size_bytes / 1024)) + ' KB') : '—';
      $1('[data-me-meta]', md).innerHTML = '<div><dt>Tipo</dt><dd>' + esc(m.mime_type) + '</dd></div><div><dt>Tamaño</dt><dd>' + kb + '</dd></div><div><dt>Subida</dt><dd>' + fmtDate(m.created_at, true) + '</dd></div>';
      const save = $1('[data-me-save]', md), del = $1('[data-me-delete]', md);
      if (save) save.onclick = async () => {
        UI.setLoading(save, true);
        const r = await api('media_update', { id: m.id, alt_text: $1('#meAlt', md).value });
        UI.setLoading(save, false);
        UI.toast(r.ok ? 'success' : 'error', r.message);
        if (r.ok) { UI.closeModal(md); reload(); }
      };
      if (del) del.onclick = async () => {
        if (!await UI.confirm({ type: 'danger', title: '¿Eliminar esta imagen?', text: 'Se quitará de todas las secciones y fotos que la usen. Esta acción no se puede deshacer.', confirmText: 'Sí, eliminar' })) return;
        const r = await api('media_delete', { id: m.id });
        UI.toast(r.ok ? 'success' : 'error', r.message);
        if (r.ok) { UI.closeModal(md); reload(); }
      };
      UI.openModal(md);
    }
    function pick(cb) {
      const root = $1('#mediaModal [data-media-picker]');
      if (!root) return;
      if (!root._load) init(root);
      pickCb = cb;
      UI.openModal('mediaModal');
      root._load();
    }
    $$('[data-media-picker][data-mode="manage"]').forEach(init);
    return { pick };
  })();

  // Campo de imagen reutilizable
  function bindImageField(box) {
    const input = $1('input[type=hidden]', box), prev = $1('.image-field__preview', box);
    const pickBtn = $1('[data-pick-image]', box), clearBtn = $1('[data-clear-image]', box);
    if (pickBtn) pickBtn.addEventListener('click', () => Media.pick(m => {
      input.value = m.id; prev.innerHTML = '<img src="' + esc(url(m.file_path)) + '" alt="">';
      input.dispatchEvent(new Event('change', { bubbles: true }));
      const f = box.closest('.field'); if (f) f.classList.remove('is-invalid');
    }));
    if (clearBtn) clearBtn.addEventListener('click', () => { input.value = ''; prev.innerHTML = '<i class="fa-regular fa-image"></i>'; input.dispatchEvent(new Event('change', { bubbles: true })); });
    prev.addEventListener('click', () => pickBtn && pickBtn.click());
  }

  /* ------------------------------------------------------------------ Paginador */
  function renderPager(el, page, pages, total, go, noun) {
    if (!el) return;
    if (pages <= 1) { el.innerHTML = '<span class="pager__info">' + total + ' ' + (noun || 'registros') + '</span>'; return; }
    const nums = [];
    for (let i = 1; i <= pages; i++) if (i === 1 || i === pages || Math.abs(i - page) <= 1) nums.push(i); else if (nums[nums.length - 1] !== '…') nums.push('…');
    el.innerHTML = '<span class="pager__info">' + total + ' ' + (noun || 'registros') + '</span><div class="pager__btns">' +
      '<button type="button" data-p="' + (page - 1) + '" ' + (page <= 1 ? 'disabled' : '') + ' aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>' +
      nums.map(n => n === '…' ? '<span>…</span>' : '<button type="button" data-p="' + n + '" class="' + (n === page ? 'is-active' : '') + '">' + n + '</button>').join('') +
      '<button type="button" data-p="' + (page + 1) + '" ' + (page >= pages ? 'disabled' : '') + ' aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button></div>';
    el.onclick = e => { const b = e.target.closest('button[data-p]'); if (b && !b.disabled) go(+b.dataset.p); };
  }

  /* ------------------------------------------------------------------ CRUD genérico */
  if (SM.page === 'crud' && SM.entity) (function (E) {
    const body = $1('#crudBody'), pager = $1('#crudPager'), filtersBox = $1('#crudFilters');
    const form = $1('#crudForm'), modal = $1('#crudModal'), saveBtn = $1('#crudSave');
    const st = { page: 1, q: '', filter: Object.assign({}, E.initialFilters || {}), editing: null, quills: {} };
    const F = {}; E.fields.forEach(f => { F[f.name] = f; if (f.options) f.map = Object.fromEntries(f.options.map(o => [o.id, o])); });
    const listFields = E.fields.filter(f => f.list);

    // Filtros
    E.fields.filter(f => f.filter).forEach(f => {
      const wrap = d.createElement('div'); wrap.className = 'toolbar__filter';
      const opts = f.type === 'bool' ? [{ id: 1, label: 'Sí' }, { id: 0, label: 'No' }] : f.options;
      wrap.innerHTML = '<select class="select2" data-filter="' + f.name + '" data-placeholder="' + esc(f.label) + '" data-allow-clear="true"><option></option>' +
        opts.map(o => '<option value="' + o.id + '"' + (o.icon ? ' data-icon="' + esc(o.icon) + '"' : '') + (o.color ? ' data-color="' + esc(o.color) + '"' : '') +
          (String(st.filter[f.name]) === String(o.id) ? ' selected' : '') + '>' + esc(o.label) + '</option>').join('') + '</select>';
      filtersBox.appendChild(wrap);
    });
    UI.initSelect2(filtersBox);
    $(filtersBox).on('change', 'select[data-filter]', function () { st.filter[this.dataset.filter] = this.value; st.page = 1; load(); });
    const search = $1('#crudSearch');
    if (search) search.addEventListener('input', debounce(() => { st.q = search.value.trim(); st.page = 1; load(); }, 350));
    $1('#crudRefresh').addEventListener('click', () => load(true));
    const newBtn = $1('#crudNew');
    if (newBtn) newBtn.addEventListener('click', () => openForm(null));

    function cell(f, row) {
      const v = row[f.name];
      switch (f.type) {
        case 'bool': {
          const can = E.perms.edit && !f.readonly;
          return '<button type="button" class="pill ' + (+v ? 'pill--on' : 'pill--off') + '"' + (can ? ' data-toggle="' + f.name + '"' : ' disabled') + '>' +
            '<i class="fa-solid ' + (+v ? 'fa-check' : 'fa-xmark') + '"></i> ' + (+v ? 'Sí' : 'No') + '</button>';
        }
        case 'select': {
          const o = f.map && f.map[v];
          if (!o) return '<span class="muted">—</span>';
          if (o.color) return '<span class="chip" style="background:' + esc(o.color) + '33;color:inherit"><span class="s2-swatch" style="background:' + esc(o.color) + ';width:10px;height:10px;border-radius:50%"></span>' + esc(o.label) + '</span>';
          return (o.icon ? '<span class="cell-ico"><i class="' + esc(o.icon) + '"></i></span>' : '') + esc(o.label);
        }
        case 'image': {
          const p = row[f.name + '__path'];
          return p ? '<img class="thumb" src="' + esc(url(p)) + '" alt="" loading="lazy">' : '<span class="thumb thumb--empty"><i class="fa-regular fa-image"></i></span>';
        }
        case 'icon': return v ? '<span class="cell-ico cell-ico--lg"><i class="' + esc(v) + '"></i></span>' : '<span class="muted">—</span>';
        case 'color': return v ? '<span class="swatch-cell"><span class="s2-swatch" style="background:' + esc(v) + '"></span>' + esc(v) + '</span>' : '';
        case 'date': return v ? '<span class="nowrap">' + fmtDate(v) + '</span>' : '<span class="muted">—</span>';
        case 'datetime': return v ? '<span class="nowrap">' + fmtDate(v, true) + '</span>' : '<span class="muted">—</span>';
        case 'time': return v ? String(v).slice(0, 5) : '<span class="muted">—</span>';
        case 'richtext': case 'textarea': { const t = String(v || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim(); return t ? '<span class="clamp">' + esc(t.length > 90 ? t.slice(0, 89) + '…' : t) + '</span>' : '<span class="muted">—</span>'; }
        case 'url': case 'link': return v ? '<span class="clamp mono">' + esc(v) + '</span>' : '<span class="muted">—</span>';
        default: return v === null || v === '' ? '<span class="muted">—</span>' : esc(v);
      }
    }
    function actions(row) {
      let h = '';
      (E.rowLinks || []).forEach(l => {
        let href = l.entity ? 'crud.php?e=' + l.entity + '&' + l.param + '=' + row.id : String(l.url).replace(/\{(\w+)\}/g, (_, k) => encodeURIComponent(row[k] == null ? '' : row[k]));
        h += '<a class="act" href="' + esc(href) + '"' + (l.url && l.url.startsWith('../') ? ' target="_blank" rel="noopener"' : '') + ' title="' + esc(l.label) + '"><i class="' + esc(l.icon) + '"></i><span>' + esc(l.label) + '</span></a>';
      });
      h += '<button type="button" class="act act--edit" data-edit="' + row.id + '" title="' + (E.perms.edit ? 'Editar' : 'Ver') + '"><i class="fa-solid ' + (E.perms.edit ? 'fa-pen' : 'fa-eye') + '"></i><span>' + (E.perms.edit ? 'Editar' : 'Ver') + '</span></button>';
      if (E.perms.delete) h += '<button type="button" class="act act--del" data-del="' + row.id + '" title="Eliminar"><i class="fa-solid fa-trash-can"></i><span>Eliminar</span></button>';
      return '<div class="acts">' + h + '</div>';
    }
    function rowTitle(row) {
      const f = listFields.find(x => ['text', 'email', 'slug'].includes(x.type)) || listFields[0];
      const v = f ? row[f.name] : row.id;
      return f && f.type === 'select' && f.map && f.map[v] ? f.map[v].label : (v || ('#' + row.id));
    }

    async function load(spin) {
      if (spin) $1('#crudRefresh i').classList.add('fa-spin');
      if (!body.children.length) body.innerHTML = '<div class="table-skel">' + Array.from({ length: 6 }, () => '<div class="skeleton" style="height:54px"></div>').join('') + '</div>';
      body.classList.add('is-loading');
      const r = await api('crud_list', { entity: E.code, page: st.page, q: st.q, filter: st.filter });
      body.classList.remove('is-loading');
      if (spin) setTimeout(() => $1('#crudRefresh i').classList.remove('fa-spin'), 400);
      if (!r.ok) { UI.toast('error', r.message); return; }
      st.rows = r.rows;
      if (!r.rows.length) {
        const filtered = st.q || Object.values(st.filter).some(v => v !== '' && v != null);
        body.innerHTML = '<div class="empty-panel"><span class="empty-panel__icon"><i class="' + esc(E.icon) + '"></i></span><h2>' + (filtered ? 'Sin resultados' : 'Aún no hay registros') + '</h2><p>' +
          (filtered ? 'Prueba con otra búsqueda o quita los filtros.' : (E.perms.create ? 'Agrega el primero con el botón "Agregar".' : 'Todavía no hay información en este módulo.')) + '</p></div>';
      } else if (E.view === 'grid') {
        body.innerHTML = '<div class="cards-grid">' + r.rows.map(row => {
          const img = E.fields.find(f => f.type === 'image');
          const p = img && row[img.name + '__path'];
          return '<article class="gcard" data-row="' + row.id + '"><div class="gcard__img">' + (p ? '<img src="' + esc(url(p)) + '" alt="" loading="lazy">' : '<i class="fa-regular fa-image"></i>') + '</div><div class="gcard__body">' +
            listFields.filter(f => f.type !== 'image').map(f => '<div class="gcard__row"><small>' + esc(f.label) + '</small><div>' + cell(f, row) + '</div></div>').join('') + '</div>' + actions(row) + '</article>';
        }).join('') + '</div>';
      } else {
        body.innerHTML = '<div class="table-wrap"><table class="table"><thead><tr>' + listFields.map(f => '<th>' + esc(f.label) + '</th>').join('') + '<th class="th-actions">Acciones</th></tr></thead><tbody>' +
          r.rows.map(row => '<tr data-row="' + row.id + '">' + listFields.map((f, i) => '<td data-label="' + esc(f.label) + '"' + (i === 0 ? ' class="td-main"' : '') + '>' + cell(f, row) + '</td>').join('') +
            '<td class="td-actions">' + actions(row) + '</td></tr>').join('') + '</tbody></table></div>';
      }
      renderPager(pager, r.page, r.pages, r.total, p => { st.page = p; load(); body.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
    }

    body.addEventListener('click', async e => {
      const ed = e.target.closest('[data-edit]'); if (ed) { openForm(+ed.dataset.edit); return; }
      const del = e.target.closest('[data-del]');
      if (del) {
        const row = st.rows.find(x => x.id == del.dataset.del);
        const ok = await UI.confirm({ type: 'danger', title: '¿Eliminar ' + E.singular + '?', text: '"' + rowTitle(row) + '" se eliminará permanentemente. Esta acción no se puede deshacer.', confirmText: 'Sí, eliminar' });
        if (!ok) return;
        const r = await api('crud_delete', { entity: E.code, id: row.id });
        UI.toast(r.ok ? 'success' : 'error', r.message);
        if (r.ok) { const tr = del.closest('[data-row]'); if (tr) tr.classList.add('is-removing'); setTimeout(load, 300); }
        return;
      }
      const tg = e.target.closest('[data-toggle]');
      if (tg) {
        const id = tg.closest('[data-row]').dataset.row;
        tg.disabled = true;
        const r = await api('crud_toggle', { entity: E.code, id, field: tg.dataset.toggle });
        tg.disabled = false;
        if (!r.ok) { UI.toast('error', r.message); return; }
        tg.className = 'pill ' + (r.value ? 'pill--on' : 'pill--off');
        tg.innerHTML = '<i class="fa-solid ' + (r.value ? 'fa-check' : 'fa-xmark') + '"></i> ' + (r.value ? 'Sí' : 'No');
        const row = st.rows.find(x => x.id == id); if (row) row[tg.dataset.toggle] = r.value;
      }
    });

    // ---------- Formulario ----------
    function input(f, val, ro) {
      const id = 'f_' + f.name, name = 'data[' + f.name + ']', dis = ro ? ' disabled' : '';
      const req = f.required && !ro ? ' required' : '';
      const v = val == null ? '' : String(val);
      const mx = f.max ? ' maxlength="' + f.max + '"' : '';
      switch (f.type) {
        case 'textarea': return '<textarea class="input" id="' + id + '" name="' + name + '" rows="4"' + mx + req + dis + '>' + esc(v) + '</textarea>';
        case 'richtext': return '<div class="rte' + (ro ? ' is-readonly' : '') + '"><div id="' + id + '" data-quill="' + f.name + '"></div></div>';
        case 'number': return '<input class="input" type="number" step="1" id="' + id + '" name="' + name + '" value="' + esc(v) + '"' + (f.min != null ? ' min="' + f.min + '"' : '') + (f.max_value != null ? ' max="' + f.max_value + '"' : '') + req + dis + '>';
        case 'decimal': return '<input class="input" type="text" inputmode="decimal" id="' + id + '" name="' + name + '" value="' + esc(v) + '"' + req + dis + '>';
        case 'email': return '<div class="input-icon"><i class="fa-regular fa-envelope"></i><input class="input" type="email" id="' + id + '" name="' + name + '" value="' + esc(v) + '"' + mx + req + dis + '></div>';
        case 'url': case 'link': return '<div class="input-icon"><i class="fa-solid fa-link"></i><input class="input" type="text" id="' + id + '" name="' + name + '" value="' + esc(v) + '"' + mx + req + dis + ' placeholder="' + (f.type === 'url' ? 'https://' : '#seccion o https://') + '"></div>';
        case 'date': return '<div class="input-icon"><i class="fa-regular fa-calendar"></i><input class="input" type="text" data-date id="' + id + '" name="' + name + '" value="' + esc(v) + '"' + req + dis + ' placeholder="Elige una fecha"></div>';
        case 'time': return '<div class="input-icon"><i class="fa-regular fa-clock"></i><input class="input" type="text" data-time id="' + id + '" name="' + name + '" value="' + esc(v.slice(0, 5)) + '"' + req + dis + ' placeholder="--:--"></div>';
        case 'datetime': return '<input class="input" type="text" id="' + id + '" value="' + (v ? fmtDate(v, true) : '—') + '" disabled>';
        case 'color': return '<div class="color-field"><input type="color" value="' + esc(v || '#9ACCCD') + '" data-color-for="' + id + '"' + dis + '><input class="input" id="' + id + '" name="' + name + '" value="' + esc(v) + '" maxlength="7" data-color-text' + req + dis + '></div>';
        case 'bool': return '<label class="switch switch--field"><input type="checkbox" id="' + id + '" name="' + name + '" value="1"' + (+v ? ' checked' : '') + dis + '><span class="switch__track"></span><span>' + esc(f.label) + '</span></label>';
        case 'password': return '<div class="input-icon"><i class="fa-solid fa-lock"></i><input class="input" type="password" id="' + id + '" name="' + name + '" autocomplete="new-password"' + dis + (st.editing ? ' placeholder="••••••••  (sin cambios)"' : ' required') + '><button type="button" class="input-icon__action" data-toggle-pass="' + id + '"><i class="fa-regular fa-eye"></i></button></div><div class="strength" data-strength-for="' + id + '"><span></span><small></small></div>';
        case 'select': return '<select class="select2" id="' + id + '" name="' + name + '" data-placeholder="Selecciona…"' + (f.required ? '' : ' data-allow-clear="true"') + req + dis + '><option></option>' +
          f.options.map(o => '<option value="' + o.id + '"' + (o.icon ? ' data-icon="' + esc(o.icon) + '"' : '') + (o.color ? ' data-color="' + esc(o.color) + '"' : '') + (String(o.id) === v ? ' selected' : '') + '>' + esc(o.label) + '</option>').join('') + '</select>' +
          (f.options.some(o => o.desc) ? '<small class="field__help field__desc" data-desc-for="' + id + '"></small>' : '');
        case 'icon': {
          const list = v && !ICONS.includes(v) ? [v].concat(ICONS) : ICONS;
          return '<select class="select2" id="' + id + '" name="' + name + '" data-icon-select data-placeholder="Elige un ícono" data-allow-clear="' + (!f.required) + '"' + req + dis + '><option></option>' +
            list.map(c => '<option value="' + esc(c) + '" data-icon="' + esc(c) + '"' + (c === v ? ' selected' : '') + '>' + esc(c.replace(/^fa-(solid|regular|brands) fa-/, '').replace(/-/g, ' ')) + '</option>').join('') + '</select>';
        }
        case 'image': {
          const p = st.editing ? st.editing[f.name + '__path'] : null;
          return '<div class="image-field" data-image-field><input type="hidden" id="' + id + '" name="' + name + '" value="' + esc(v) + '"><div class="image-field__preview">' + (p ? '<img src="' + esc(url(p)) + '" alt="">' : '<i class="fa-regular fa-image"></i>') + '</div>' +
            (ro ? '' : '<div class="image-field__actions"><button type="button" class="btn btn--secondary btn--sm" data-pick-image><i class="fa-solid fa-images"></i> Elegir imagen</button>' + (f.required ? '' : '<button type="button" class="btn btn--ghost btn--sm" data-clear-image><i class="fa-solid fa-xmark"></i> Quitar</button>') + '</div>') + '</div>';
        }
        default: return '<input class="input" type="text" id="' + id + '" name="' + name + '" value="' + esc(v) + '"' + mx + req + dis + (f.type === 'slug' ? ' data-slug' : '') + '>';
      }
    }

    async function openForm(id) {
      let row = null;
      if (id) {
        const r = await api('crud_get', { entity: E.code, id });
        if (!r.ok) { UI.toast('error', r.message); return; }
        row = r.row;
      }
      st.editing = row;
      const ro = id ? !E.perms.edit : !E.perms.create;
      $1('#crudModalTitle').textContent = (id ? (ro ? 'Detalle de ' : 'Editar ') : 'Nuevo: ') + E.singular;
      $1('#crudModalSub').textContent = id ? '"' + rowTitle(row) + '" · registro #' + id : 'Completa los datos y guarda.';
      saveBtn.hidden = ro;
      // Destruir componentes previos
      $(form).find('select.select2-hidden-accessible').select2('destroy');
      $$('[data-date],[data-time]', form).forEach(i => i._flatpickr && i._flatpickr.destroy());
      st.quills = {};
      form.innerHTML = E.fields.filter(f => !(f.type === 'datetime' && !id)).map(f => {
        let val = row ? row[f.name] : (f.default != null ? f.default : '');
        if (!row && st.filter[f.name] !== undefined && st.filter[f.name] !== '') val = st.filter[f.name];
        const fro = ro || f.readonly;
        const full = f.full || f.type === 'image' || f.type === 'textarea';
        return '<div class="field' + (full ? ' field--full' : '') + (f.type === 'bool' ? ' field--switch' : '') + '" data-field="' + f.name + '">' +
          (f.type !== 'bool' ? '<label class="field__label" for="f_' + f.name + '">' + esc(f.label) + (f.required && !fro ? ' <span class="req">*</span>' : '') + '</label>' : '') +
          input(f, val, fro) + (f.help && !fro ? '<small class="field__help">' + esc(f.help) + '</small>' : '') + '</div>';
      }).join('');
      UI.initSelect2(form, { templateResult: UI.s2Format });
      $$('[data-image-field]', form).forEach(bindImageField);
      $$('[data-date]', form).forEach(i => flatpickr(i, { locale: 'es', dateFormat: 'Y-m-d', altInput: true, altFormat: 'j M Y', altInputClass: 'input', disableMobile: true, allowInput: false }));
      $$('[data-time]', form).forEach(i => flatpickr(i, { locale: 'es', enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true, disableMobile: true }));
      $$('[data-quill]', form).forEach(el => {
        const q = new Quill(el, { theme: 'snow', readOnly: ro, placeholder: 'Escribe aquí…', modules: { toolbar: ro ? false : [[{ header: [2, 3, 4, false] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'link'], [{ align: [] }], ['clean']] } });
        const v = row ? row[el.dataset.quill] : '';
        if (v) q.clipboard.dangerouslyPasteHTML(v);
        st.quills[el.dataset.quill] = q;
      });
      $$('[data-strength-for]', form).forEach(b => bindStrength(d.getElementById(b.dataset.strengthFor)));
      // Descripción del tipo seleccionado
      $$('[data-desc-for]', form).forEach(h => {
        const sel = d.getElementById(h.dataset.descFor), f = F[sel.id.slice(2)];
        const upd = () => { const o = f.map[sel.value]; h.innerHTML = o && o.desc ? '<i class="fa-solid fa-circle-info"></i> ' + esc(o.desc) : ''; };
        $(sel).on('change', upd); upd();
      });
      // Slug automático
      $$('[data-slug]', form).forEach(s => {
        const f = F[s.id.slice(2)], src = f.from && d.getElementById('f_' + f.from);
        let touched = !!s.value;
        s.addEventListener('input', () => { touched = true; });
        s.addEventListener('blur', () => { s.value = slug(s.value); });
        if (src) src.addEventListener('input', () => { if (!touched) s.value = slug(src.value); });
      });
      UI.openModal(modal);
    }
    function slug(t) { return String(t).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/ñ/g, 'n').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''); }

    form.addEventListener('submit', async e => {
      e.preventDefault();
      // Validación visual
      let bad = null;
      E.fields.forEach(f => {
        if (!f.required || f.readonly || (f.type === 'password' && st.editing)) return;
        let v;
        if (f.type === 'richtext') v = st.quills[f.name] && st.quills[f.name].getText().trim();
        else { const el = d.getElementById('f_' + f.name); v = el && el.value.trim(); }
        if (!v) { const fe = $1('[data-field="' + f.name + '"]', form); fe.classList.add('is-invalid'); bad = bad || f; }
      });
      if (bad) { UI.toast('warning', 'Completa el campo "' + bad.label + '".', 'Faltan datos'); scrollToField(bad.name); return; }
      const fd = new FormData(form);
      Object.entries(st.quills).forEach(([k, q]) => fd.set('data[' + k + ']', q.getText().trim() ? q.root.innerHTML : ''));
      E.fields.filter(f => f.type === 'bool' && !f.readonly).forEach(f => { if (!fd.has('data[' + f.name + ']')) fd.append('data[' + f.name + ']', '0'); });
      fd.append('entity', E.code);
      if (st.editing) fd.append('id', st.editing.id);
      UI.setLoading(saveBtn, true);
      const r = await api('crud_save', fd);
      UI.setLoading(saveBtn, false);
      if (!r.ok) {
        UI.toast('error', r.message, 'No se pudo guardar');
        if (r.field) { const fe = $1('[data-field="' + r.field + '"]', form); if (fe) { fe.classList.add('is-invalid'); scrollToField(r.field); } }
        return;
      }
      UI.closeModal(modal);
      UI.toast('success', r.message);
      load();
    });
    function scrollToField(n) { const fe = $1('[data-field="' + n + '"]', form); if (fe) fe.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
    form.addEventListener('click', e => { const f = e.target.closest('.field.is-invalid'); if (f) f.classList.remove('is-invalid'); });
    $(form).on('change', 'select', function () { const f = this.closest('.field'); if (f) f.classList.remove('is-invalid'); });

    load();
    if (E.openNew && E.perms.create) openForm(null);
  })(SM.entity);

  /* ------------------------------------------------------------------ Color (texto + selector) */
  d.addEventListener('input', e => {
    if (e.target.matches('input[type=color][data-color-for]')) {
      const t = d.getElementById(e.target.dataset.colorFor); t.value = e.target.value.toUpperCase(); t.dispatchEvent(new Event('change', { bubbles: true }));
    } else if (e.target.matches('[data-color-text]')) {
      const p = e.target.parentElement.querySelector('input[type=color]');
      if (/^#[0-9a-f]{6}$/i.test(e.target.value)) { p.value = e.target.value; e.target.dispatchEvent(new Event('change', { bubbles: true })); }
    }
  });

  /* ------------------------------------------------------------------ Configuración */
  if (SM.page === 'settings') {
    const f = $1('#settingsForm');
    UI.initSelect2(f);
    $$('[data-image-field]', f).forEach(bindImageField);
    // Vista previa de tipografías
    $$('[data-font-select]', f).forEach(s => {
      const fam = () => s.value;
      $(s).on('change', () => loadFont(fam()));
      loadFont(fam());
    });
    function loadFont(name) {
      if (!/^[A-Za-z0-9 ]+$/.test(name) || d.querySelector('link[data-font="' + name + '"]')) return;
      const l = d.createElement('link'); l.rel = 'stylesheet'; l.dataset.font = name;
      l.href = 'https://fonts.googleapis.com/css2?family=' + name.replace(/ /g, '+') + ':wght@400;700&display=swap';
      d.head.appendChild(l);
    }
    // Pestañas
    $$('.tabs__btn').forEach(b => b.addEventListener('click', () => {
      $$('.tabs__btn').forEach(x => x.classList.toggle('is-active', x === b));
      $$('.tab-panel').forEach(p => p.classList.toggle('is-active', p.id === b.dataset.tab));
      b.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }));
    // Vista previa de la paleta
    function palette() {
      const pv = $1('[data-palette-preview]'); if (!pv) return;
      const g = k => { const i = d.getElementById('st_' + k); return i && /^#[0-9a-f]{6}$/i.test(i.value) ? i.value : null; };
      pv.style.setProperty('--pv-bg', g('color_background') || '#F0F8FB');
      pv.style.setProperty('--pv-primary', g('color_primary') || '#1C3762');
      pv.style.setProperty('--pv-secondary', g('color_secondary') || '#9ACCCD');
      pv.style.setProperty('--pv-accent', g('color_accent') || '#F5C15C');
    }
    f.addEventListener('change', palette); f.addEventListener('input', palette); palette();
    f.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = $1('.sticky-save .btn', f);
      UI.setLoading(btn, true);
      const r = await api('settings_save', new FormData(f));
      UI.setLoading(btn, false);
      if (!r.ok) {
        UI.toast('error', r.message, 'No se pudo guardar');
        if (r.field) { const el = d.getElementById('st_' + r.field); if (el) { const p = el.closest('.tab-panel'); $1('[data-tab="' + p.id + '"]').click(); el.closest('.field').classList.add('is-invalid'); } }
        return;
      }
      UI.toast('success', r.message);
    });
  }

  // Botón de correo de prueba (Configuración → Notificaciones)
  const mailTest = $1('#mailTest');
  if (mailTest) mailTest.addEventListener('click', async () => {
    UI.setLoading(mailTest, true);
    const r = await api('mail_test', {});
    UI.setLoading(mailTest, false);
    if (r.ok) UI.alert('success', '¡Correo enviado!', r.message);
    else UI.alert('error', 'No se pudo enviar el correo', r.message);
  });

  /* ------------------------------------------------------------------ Permisos */
  if (SM.page === 'permissions') {
    UI.initSelect2(d);
    $('#roleSelect').on('change', function () { location.href = 'permissions.php?role=' + this.value; });
    const form = $1('#permForm');
    // Si se activa crear/editar/eliminar, activar también "ver"; si se quita "ver", quitar el resto
    form.addEventListener('change', e => {
      const cb = e.target; if (!cb.matches('input[type=checkbox]')) return;
      const row = cb.closest('.perm-row');
      const view = $1('[data-perm="view"]', row);
      if (cb.checked && cb !== view) view.checked = true;
      if (!cb.checked && cb === view) $$('input[type=checkbox]', row).forEach(x => { x.checked = false; });
    });
    $$('[data-perm-all]').forEach(b => b.addEventListener('click', () => $$('input[type=checkbox]:not(:disabled)', form).forEach(x => { x.checked = b.dataset.permAll === '1'; })));
    $$('[data-perm-group]').forEach(b => b.addEventListener('click', () => {
      const boxes = $$('input[type=checkbox]', b.closest('.perm-group'));
      const all = boxes.every(x => x.checked);
      boxes.forEach(x => { x.checked = !all; });
    }));
    const save = $1('#permSave');
    if (save) save.addEventListener('click', async () => {
      const fd = new FormData(form); fd.append('role_id', form.dataset.role);
      UI.setLoading(save, true);
      const r = await api('permissions_save', fd);
      UI.setLoading(save, false);
      UI.toast(r.ok ? 'success' : 'error', r.message);
    });
  }

  /* ------------------------------------------------------------------ Perfil */
  if (SM.page === 'profile') {
    const f = $1('#profileForm');
    bindStrength($1('#pfNew'));
    f.addEventListener('submit', async e => {
      e.preventDefault();
      if (!$1('#pfCurrent').value) { UI.toast('warning', 'Escribe tu contraseña actual para confirmar los cambios.', 'Falta un dato'); $1('#pfCurrent').closest('.field').classList.add('is-invalid'); return; }
      const btn = $1('button[type=submit]', f);
      UI.setLoading(btn, true);
      const r = await api('profile_save', new FormData(f));
      UI.setLoading(btn, false);
      if (!r.ok) { UI.toast('error', r.message); UI.markInvalid(f, r.field); return; }
      UI.toast('success', r.message);
      ['pfNew', 'pfConfirm', 'pfCurrent'].forEach(i => { d.getElementById(i).value = ''; });
    });
  }
})(window, document, window.jQuery);
