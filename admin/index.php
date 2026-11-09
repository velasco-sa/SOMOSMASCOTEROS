<?php
/**
 * Inicio de sesión del panel (solo accesible escribiendo la dirección).
 */
declare(strict_types=1);
require __DIR__ . '/includes/init.php';

if (current_user()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$email = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $ip = client_ip();
    $since = date('Y-m-d H:i:s', time() - LOGIN_WINDOW_MINUTES * 60);
    $fails = (int)db_value(
        'SELECT COUNT(*) FROM login_attempts WHERE was_successful = 0 AND attempted_at >= ? AND (ip_address = ? OR email = ?)',
        [$since, $ip, $email]
    );
    if (!csrf_check()) {
        $error = 'La página expiró. Inténtalo de nuevo.';
    } elseif ($fails >= LOGIN_MAX_ATTEMPTS) {
        $error = 'Demasiados intentos fallidos. Espera ' . LOGIN_WINDOW_MINUTES . ' minutos e inténtalo de nuevo.';
    } else {
        $user = db_one('SELECT id, password_hash, is_active FROM users WHERE email = ?', [$email]);
        // Se verifica siempre un hash para no revelar si el correo existe (tiempo constante)
        $hash = $user['password_hash'] ?? '$2y$12$m/SK7AUUWjZdHcx3p5iu4uTuc.C/blYBl0hfeBhwGPYrcELsJPpUu';
        $valid = password_verify($password, $hash) && $user && (int)$user['is_active'] === 1;
        db_exec('INSERT INTO login_attempts (email, ip_address, was_successful) VALUES (?,?,?)', [mb_substr($email, 0, 160), $ip, $valid ? 1 : 0]);
        if ($valid) {
            if (password_needs_rehash($hash, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT)) {
                db_exec('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT), $user['id']]);
            }
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$user['id'];
            $_SESSION['last_seen'] = time();
            unset($_SESSION['csrf']);
            db_exec('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$user['id']]);
            db_exec('DELETE FROM login_attempts WHERE attempted_at < ?', [date('Y-m-d H:i:s', time() - 86400 * 30)]);
            header('Location: dashboard.php');
            exit;
        }
        $left = LOGIN_MAX_ATTEMPTS - $fails - 1;
        $error = 'Correo o contraseña incorrectos.' . ($left > 0 && $left < 3 ? " Te quedan $left intentos." : '');
    }
}

$logo = setting('logo');
$siteName = setting('site_name', 'Somos Mascoteros');
admin_header('Iniciar sesión', '');
?>
<div class="login">
  <div class="login__art" aria-hidden="true">
    <div class="login__aurora"><span></span><span></span><span></span></div>
    <div class="login__art-inner">
      <?php if ($logo): ?><img src="<?= e(url_for($logo)) ?>" alt="" class="login__art-logo"><?php endif; ?>
      <h2><?= e($siteName) ?></h2>
      <p>Administra textos, imágenes, reservas y todo el contenido de tu sitio sin tocar una sola línea de código.</p>
      <ul>
        <li><i class="fa-solid fa-pen-ruler"></i> Contenido 100% editable</li>
        <li><i class="fa-solid fa-calendar-check"></i> Reservas y disponibilidad</li>
        <li><i class="fa-solid fa-user-shield"></i> Usuarios, roles y permisos</li>
      </ul>
    </div>
  </div>
  <div class="login__panel">
    <form method="post" class="login__card" id="loginForm" novalidate autocomplete="on">
      <?php if ($logo): ?><img src="<?= e(url_for($logo)) ?>" alt="<?= e($siteName) ?>" class="login__logo"><?php endif; ?>
      <h1>¡Hola de nuevo!</h1>
      <p class="login__sub">Ingresa tus datos para entrar al panel.</p>
      <?php if ($error): ?><div class="alert alert--error" role="alert"><i class="fa-solid fa-circle-exclamation"></i><span><?= e($error) ?></span></div><?php endif; ?>
      <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
      <div class="field">
        <label class="field__label" for="email">Correo electrónico</label>
        <div class="input-icon"><i class="fa-regular fa-envelope"></i><input class="input" type="email" id="email" name="email" value="<?= e($email) ?>" required autocomplete="username" autofocus></div>
      </div>
      <div class="field">
        <label class="field__label" for="password">Contraseña</label>
        <div class="input-icon"><i class="fa-solid fa-lock"></i><input class="input" type="password" id="password" name="password" required autocomplete="current-password">
          <button type="button" class="input-icon__action" data-toggle-pass="password" aria-label="Mostrar contraseña"><i class="fa-regular fa-eye"></i></button></div>
      </div>
      <button class="btn btn--primary btn--block" type="submit"><i class="fa-solid fa-right-to-bracket"></i> Entrar</button>
      <p class="login__note"><i class="fa-solid fa-shield-halved"></i> Conexión protegida · Las contraseñas se guardan cifradas</p>
    </form>
  </div>
</div>
<?php admin_footer(['page' => 'login']); ?>
