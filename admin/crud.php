<?php
/**
 * Pantalla genérica de administración para cualquier módulo.
 */
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
require __DIR__ . '/includes/crud.php';
require_login();

$code = (string)($_GET['e'] ?? '');
$cfg = entity($code);
if (!$cfg) {
    header('Location: dashboard.php');
    exit;
}
require_perm($code);
$module = db_one('SELECT name, icon, description FROM modules WHERE code = ?', [$code]) ?? ['name' => ucfirst($code), 'icon' => 'fa-solid fa-table', 'description' => ''];
$meta = entity_meta($cfg);
$meta['title'] = $module['name'];
$meta['icon'] = $module['icon'];

// Filtros iniciales recibidos por la URL (desde los botones de cada fila)
$initial = [];
foreach ($_GET as $k => $v) {
    if ($k !== 'e' && $k !== 'new' && preg_match('/^[a-z_]+_id$|^is_[a-z_]+$/', $k) && isset($cfg['fields'][$k]) && is_scalar($v)) {
        $initial[$k] = (int)$v;
    }
}
$meta['initialFilters'] = $initial;
$meta['openNew'] = !empty($_GET['new']);

admin_header($module['name'], $code, ['subtitle' => $module['description'] ?? '']);
?>
<div class="crud" id="crud">
  <?php if (!empty($cfg['intro'])): ?>
    <div class="alert alert--info crud__intro"><i class="fa-solid fa-lightbulb"></i><span><?= e($cfg['intro']) ?></span></div>
  <?php endif; ?>
  <div class="toolbar card">
    <div class="toolbar__search"<?= $meta['searchable'] ? '' : ' hidden' ?>>
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="search" class="input" id="crudSearch" placeholder="Buscar…" autocomplete="off">
    </div>
    <div class="toolbar__filters" id="crudFilters"></div>
    <div class="toolbar__actions">
      <button type="button" class="btn btn--ghost btn--icon" id="crudRefresh" title="Actualizar"><i class="fa-solid fa-rotate"></i></button>
      <?php if ($meta['perms']['create']): ?>
        <button type="button" class="btn btn--primary" id="crudNew"><i class="fa-solid fa-plus"></i> <span>Agregar <?= e($cfg['singular']) ?></span></button>
      <?php endif; ?>
    </div>
  </div>
  <div class="card table-card">
    <div id="crudBody" class="crud__body"></div>
    <footer class="pager" id="crudPager"></footer>
  </div>
</div>

<!-- Modal de formulario -->
<div class="modal modal--lg" id="crudModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="crudModalTitle">
  <div class="modal__backdrop" data-modal-close></div>
  <div class="modal__dialog">
    <button class="modal__close" data-modal-close aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    <div class="modal__head">
      <span class="modal__icon"><i class="<?= e($module['icon']) ?>"></i></span>
      <div><h3 id="crudModalTitle"></h3><p class="modal__sub" id="crudModalSub"></p></div>
    </div>
    <form id="crudForm" class="form-grid" novalidate autocomplete="off"></form>
    <div class="modal__foot">
      <button type="button" class="btn btn--ghost" data-modal-close>Cancelar</button>
      <button type="submit" form="crudForm" class="btn btn--primary" id="crudSave"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/media_modal.php'; ?>
<?php admin_footer(['page' => 'crud', 'entity' => $meta]); ?>
