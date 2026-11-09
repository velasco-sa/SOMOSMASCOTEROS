<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
$u = require_login();
$last = db_value('SELECT last_login_at FROM users WHERE id = ?', [$u['id']]);
admin_header('Mi perfil', '', ['subtitle' => 'Tus datos de acceso y contraseña']);
?>
<div class="profile">
  <aside class="card profile__card">
    <span class="avatar avatar--xl"><?= e(mb_strtoupper(mb_substr($u['full_name'], 0, 1))) ?></span>
    <h3><?= e($u['full_name']) ?></h3>
    <p><?= e($u['email']) ?></p>
    <span class="chip chip--accent"><i class="fa-solid fa-user-shield"></i> <?= e($u['role_name']) ?></span>
    <?php if ($last): ?><small class="profile__last"><i class="fa-regular fa-clock"></i> Último acceso: <?= e(date('d/m/Y H:i', strtotime($last))) ?></small><?php endif; ?>
  </aside>
  <form class="card profile__form" id="profileForm" novalidate>
    <header class="card__head"><h3><i class="fa-solid fa-id-card"></i> Datos personales</h3></header>
    <div class="form-grid">
      <div class="field"><label class="field__label" for="pfName">Nombre completo</label><input class="input" id="pfName" name="full_name" value="<?= e($u['full_name']) ?>" required maxlength="120"></div>
      <div class="field"><label class="field__label" for="pfEmail">Correo</label><input class="input" id="pfEmail" name="email" type="email" value="<?= e($u['email']) ?>" required maxlength="160"></div>
    </div>
    <header class="card__head card__head--sep"><h3><i class="fa-solid fa-key"></i> Cambiar contraseña</h3></header>
    <div class="form-grid">
      <div class="field"><label class="field__label" for="pfNew">Nueva contraseña</label><div class="input-icon"><i class="fa-solid fa-lock"></i><input class="input" id="pfNew" name="new_password" type="password" autocomplete="new-password" placeholder="Déjala vacía para no cambiarla"><button type="button" class="input-icon__action" data-toggle-pass="pfNew"><i class="fa-regular fa-eye"></i></button></div>
        <div class="strength" data-strength-for="pfNew"><span></span><small></small></div></div>
      <div class="field"><label class="field__label" for="pfConfirm">Confirmar nueva contraseña</label><div class="input-icon"><i class="fa-solid fa-lock"></i><input class="input" id="pfConfirm" name="confirm_password" type="password" autocomplete="new-password"></div></div>
      <div class="field field--full"><label class="field__label" for="pfCurrent">Contraseña actual <span class="req">*</span></label><div class="input-icon"><i class="fa-solid fa-shield-halved"></i><input class="input" id="pfCurrent" name="current_password" type="password" required autocomplete="current-password" placeholder="Necesaria para guardar cualquier cambio"></div></div>
    </div>
    <div class="modal__foot"><button type="submit" class="btn btn--primary"><i class="fa-solid fa-floppy-disk"></i> Guardar cambios</button></div>
  </form>
</div>
<?php admin_footer(['page' => 'profile']); ?>
