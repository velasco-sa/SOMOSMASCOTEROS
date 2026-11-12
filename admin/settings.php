<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
require_login();
require_perm('settings');

$groups = db_all('SELECT * FROM setting_groups ORDER BY sort_order, id');
$rows = db_all('SELECT s.*, m.file_path FROM settings s LEFT JOIN media m ON m.id = s.media_id ORDER BY s.setting_group_id, s.sort_order, s.id');
$by = [];
foreach ($rows as $r) { $by[$r['setting_group_id']][] = $r; }
$canEdit = can('settings', 'edit');
$fonts = ['Montserrat', 'Raleway', 'Poppins', 'Inter', 'Nunito', 'Quicksand', 'Outfit', 'Plus Jakarta Sans', 'DM Sans', 'Manrope', 'Lato', 'Open Sans', 'Roboto', 'Playfair Display', 'Fredoka', 'Baloo 2', 'Sora', 'Urbanist'];

admin_header('Configuración del sitio', 'settings', ['subtitle' => 'Identidad, colores, tipografías, SEO y textos generales']);
?>
<form id="settingsForm" class="settings" novalidate>
  <nav class="tabs" role="tablist">
    <?php foreach ($groups as $k => $g): if (empty($by[$g['id']])) continue; ?>
      <button type="button" class="tabs__btn<?= $k === 0 ? ' is-active' : '' ?>" data-tab="g<?= (int)$g['id'] ?>" role="tab"><i class="<?= e($g['icon']) ?>"></i><span><?= e($g['name']) ?></span></button>
    <?php endforeach; ?>
  </nav>
  <?php foreach ($groups as $k => $g): if (empty($by[$g['id']])) continue; ?>
    <section class="card tab-panel<?= $k === 0 ? ' is-active' : '' ?>" id="g<?= (int)$g['id'] ?>" role="tabpanel">
      <header class="card__head"><h3><i class="<?= e($g['icon']) ?>"></i> <?= e($g['name']) ?></h3></header>
      <div class="form-grid">
        <?php foreach ($by[$g['id']] as $s): $name = 'settings[' . $s['setting_key'] . ']'; $id = 'st_' . $s['setting_key']; $dis = $canEdit ? '' : ' disabled'; ?>
          <div class="field<?= in_array($s['input_type'], ['textarea', 'image'], true) ? ' field--full' : '' ?>">
            <?php if ($s['input_type'] !== 'bool'): ?><label class="field__label" for="<?= e($id) ?>"><?= e($s['label']) ?></label><?php endif; ?>
            <?php switch ($s['input_type']):
              case 'textarea': ?>
                <textarea class="input" id="<?= e($id) ?>" name="<?= e($name) ?>" rows="3"<?= $dis ?>><?= e($s['setting_value']) ?></textarea>
              <?php break; case 'color': ?>
                <div class="color-field"><input type="color" value="<?= e(safe_color((string)$s['setting_value'], '#000000')) ?>" data-color-for="<?= e($id) ?>"<?= $dis ?>><input class="input" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e($s['setting_value']) ?>" maxlength="7" data-color-text<?= $dis ?>></div>
              <?php break; case 'bool': ?>
                <label class="switch switch--field"><input type="hidden" name="<?= e($name) ?>" value="0"><input type="checkbox" id="<?= e($id) ?>" name="<?= e($name) ?>" value="1" <?= $s['setting_value'] === '1' ? 'checked' : '' ?><?= $dis ?>><span class="switch__track"></span><span><?= e($s['label']) ?></span></label>
              <?php break; case 'font': ?>
                <select class="select2" id="<?= e($id) ?>" name="<?= e($name) ?>" data-font-select<?= $dis ?>>
                  <?php $cur = (string)$s['setting_value']; $list = in_array($cur, $fonts, true) ? $fonts : array_merge([$cur], $fonts);
                  foreach ($list as $f): ?><option value="<?= e($f) ?>" <?= $f === $cur ? 'selected' : '' ?>><?= e($f) ?></option><?php endforeach; ?>
                </select>
              <?php break; case 'image': ?>
                <div class="image-field" data-image-field>
                  <input type="hidden" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= (int)$s['media_id'] ?: '' ?>">
                  <div class="image-field__preview"><?php if ($s['file_path']): ?><img src="<?= e(url_for($s['file_path'])) ?>" alt=""><?php else: ?><i class="fa-regular fa-image"></i><?php endif; ?></div>
                  <?php if ($canEdit): ?><div class="image-field__actions"><button type="button" class="btn btn--secondary btn--sm" data-pick-image><i class="fa-solid fa-images"></i> Elegir imagen</button><button type="button" class="btn btn--ghost btn--sm" data-clear-image><i class="fa-solid fa-xmark"></i> Quitar</button></div><?php endif; ?>
                </div>
              <?php break; case 'number': ?>
                <input class="input" type="number" min="0" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e($s['setting_value']) ?>"<?= $dis ?>>
              <?php break; default: ?>
                <input class="input" type="<?= $s['input_type'] === 'email' ? 'email' : ($s['input_type'] === 'url' ? 'url' : 'text') ?>" id="<?= e($id) ?>" name="<?= e($name) ?>" value="<?= e($s['setting_value']) ?>" maxlength="500"<?= $dis ?>>
            <?php endswitch; ?>
            <?php if ($s['help_text']): ?><small class="field__help"><?= e($s['help_text']) ?></small><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (in_array('notify_email', array_column($by[$g['id']], 'setting_key'), true)): ?>
        <div class="mail-test">
          <div>
            <strong><i class="fa-solid fa-paper-plane"></i> Probar los avisos</strong>
            <p>Guarda primero el correo y luego envía una prueba. Si no llega en un par de minutos, revisa la carpeta de spam.</p>
          </div>
          <?php if ($canEdit): ?><button type="button" class="btn btn--secondary" id="mailTest"><i class="fa-solid fa-envelope-circle-check"></i> Enviar correo de prueba</button><?php endif; ?>
        </div>
      <?php endif; ?>
      <?php if ((int)$g['sort_order'] === 2 || stripos($g['name'], 'color') !== false): ?>
        <div class="palette-preview" data-palette-preview>
          <span style="background:var(--pv-bg)">Fondo</span><span style="background:var(--pv-primary);color:#fff">Principal</span><span style="background:var(--pv-secondary)">Secundario</span><span style="background:var(--pv-accent)">Acento</span>
        </div>
      <?php endif; ?>
    </section>
  <?php endforeach; ?>
  <?php if ($canEdit): ?>
    <div class="sticky-save"><span><i class="fa-solid fa-circle-info"></i> Los cambios se aplican al sitio al guardar.</span><button type="submit" class="btn btn--primary"><i class="fa-solid fa-floppy-disk"></i> Guardar configuración</button></div>
  <?php endif; ?>
</form>
<?php include __DIR__ . '/includes/media_modal.php'; ?>
<?php admin_footer(['page' => 'settings']); ?>
