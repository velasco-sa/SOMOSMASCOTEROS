<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
require_login();
require_perm('roles');

$roles = db_all('SELECT id, name, is_system FROM roles ORDER BY id');
$roleId = (int)($_GET['role'] ?? ($roles[1]['id'] ?? $roles[0]['id'] ?? 0));
$role = db_one('SELECT * FROM roles WHERE id = ?', [$roleId]);
if (!$role) { header('Location: crud.php?e=roles'); exit; }
$mods = db_all('SELECT m.id, m.name, m.icon, m.description, g.name AS grp FROM modules m JOIN module_groups g ON g.id = m.module_group_id ORDER BY g.sort_order, m.sort_order, m.id');
$perms = db_all('SELECT id, code, name FROM permissions ORDER BY id');
$granted = [];
foreach (db_all('SELECT module_id, permission_id FROM role_module_permissions WHERE role_id = ?', [$roleId]) as $g) {
    $granted[$g['module_id'] . ':' . $g['permission_id']] = true;
}
$system = (int)$role['is_system'] === 1;
$canEdit = can('roles', 'edit') && !$system;
$byGroup = [];
foreach ($mods as $m) { $byGroup[$m['grp']][] = $m; }

admin_header('Permisos por rol', 'roles', ['subtitle' => 'Define qué puede hacer cada rol en cada módulo']);
?>
<div class="toolbar card">
  <div class="field toolbar__role">
    <select class="select2" id="roleSelect" data-placeholder="Rol">
      <?php foreach ($roles as $r): ?><option value="<?= (int)$r['id'] ?>" data-icon="<?= $r['is_system'] ? 'fa-solid fa-crown' : 'fa-solid fa-user-shield' ?>" <?= (int)$r['id'] === $roleId ? 'selected' : '' ?>><?= e($r['name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="toolbar__actions">
    <a href="crud.php?e=roles" class="btn btn--ghost"><i class="fa-solid fa-arrow-left"></i> <span>Roles</span></a>
    <?php if ($canEdit): ?>
      <button type="button" class="btn btn--ghost" data-perm-all="1"><i class="fa-solid fa-check-double"></i> <span>Todo</span></button>
      <button type="button" class="btn btn--ghost" data-perm-all="0"><i class="fa-solid fa-eraser"></i> <span>Nada</span></button>
      <button type="button" class="btn btn--primary" id="permSave"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    <?php endif; ?>
  </div>
</div>
<?php if ($system): ?>
  <div class="alert alert--warning crud__intro"><i class="fa-solid fa-crown"></i><span>"<?= e($role['name']) ?>" es un rol protegido: siempre tiene acceso total a todos los módulos.</span></div>
<?php endif; ?>
<form id="permForm" data-role="<?= $roleId ?>">
  <?php foreach ($byGroup as $grp => $list): ?>
    <section class="card perm-group">
      <header class="card__head"><h3><?= e($grp) ?></h3>
        <?php if ($canEdit): ?><button type="button" class="btn btn--ghost btn--xs" data-perm-group><i class="fa-solid fa-check"></i> Marcar grupo</button><?php endif; ?>
      </header>
      <div class="perm-table">
        <div class="perm-row perm-row--head"><span>Módulo</span><?php foreach ($perms as $p): ?><span><?= e($p['name']) ?></span><?php endforeach; ?></div>
        <?php foreach ($list as $m): ?>
          <div class="perm-row">
            <span class="perm-row__mod"><span class="s2-ico"><i class="<?= e($m['icon']) ?>"></i></span><span><strong><?= e($m['name']) ?></strong><small><?= e($m['description']) ?></small></span></span>
            <?php foreach ($perms as $p): $key = $m['id'] . ':' . $p['id']; ?>
              <label class="switch perm-switch" title="<?= e($p['name'] . ' · ' . $m['name']) ?>">
                <input type="checkbox" name="grants[]" value="<?= e($key) ?>" data-perm="<?= e($p['code']) ?>" <?= $system || isset($granted[$key]) ? 'checked' : '' ?> <?= $canEdit ? '' : 'disabled' ?>>
                <span class="switch__track"></span><span class="perm-switch__lbl"><?= e($p['name']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
</form>
<?php admin_footer(['page' => 'permissions']); ?>
