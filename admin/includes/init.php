<?php
/**
 * Inicialización del panel: autenticación, permisos y diseño.
 */
declare(strict_types=1);
define('BASE_PREFIX', '../');
require dirname(__DIR__, 2) . '/includes/bootstrap.php';

// El panel nunca debe aparecer en buscadores
header('X-Robots-Tag: noindex, nofollow, noarchive, nosnippet');
header('Cache-Control: no-store, no-cache, must-revalidate');

const SESSION_IDLE_SECONDS = 7200;   // 2 horas sin actividad
const LOGIN_MAX_ATTEMPTS   = 5;      // intentos fallidos permitidos...
const LOGIN_WINDOW_MINUTES = 15;     // ...en esta ventana de tiempo

/** Usuario autenticado (o null). Se consulta en cada petición para aplicar cambios al instante. */
function current_user(): ?array
{
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    $user = null;
    if (empty($_SESSION['uid'])) {
        return null;
    }
    if (!empty($_SESSION['last_seen']) && time() - $_SESSION['last_seen'] > SESSION_IDLE_SECONDS) {
        logout_user();
        return null;
    }
    $_SESSION['last_seen'] = time();
    $row = db_one(
        'SELECT u.id, u.full_name, u.email, u.role_id, u.is_active, r.name AS role_name, r.is_system
         FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?',
        [(int)$_SESSION['uid']]
    );
    if (!$row || !(int)$row['is_active']) {
        logout_user();
        return null;
    }
    $user = $row;
    return $user;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Lista de permisos del usuario: ['modulo' => ['view'=>true, ...]]. */
function user_permissions(): array
{
    static $perms = null;
    if ($perms !== null) {
        return $perms;
    }
    $perms = [];
    $u = current_user();
    if (!$u) {
        return $perms;
    }
    if ((int)$u['is_system'] === 1) {
        $rows = db_all('SELECT m.code AS module, p.code AS perm FROM modules m CROSS JOIN permissions p WHERE m.is_active = 1');
    } else {
        $rows = db_all(
            'SELECT m.code AS module, p.code AS perm FROM role_module_permissions rmp
             JOIN modules m ON m.id = rmp.module_id AND m.is_active = 1
             JOIN permissions p ON p.id = rmp.permission_id
             WHERE rmp.role_id = ?',
            [$u['role_id']]
        );
    }
    foreach ($rows as $r) {
        $perms[$r['module']][$r['perm']] = true;
    }
    return $perms;
}

function can(string $module, string $perm = 'view'): bool
{
    $p = user_permissions();
    return !empty($p[$module][$perm]);
}

/** Exige sesión iniciada (redirige al login si no la hay). */
function require_login(): array
{
    $u = current_user();
    if (!$u) {
        if (is_api_request()) {
            json_out(['ok' => false, 'message' => 'Tu sesión terminó. Vuelve a iniciar sesión.', 'login' => true], 401);
        }
        header('Location: index.php');
        exit;
    }
    return $u;
}

function require_perm(string $module, string $perm = 'view'): void
{
    if (!can($module, $perm)) {
        if (is_api_request()) {
            json_out(['ok' => false, 'message' => 'No tienes permiso para realizar esta acción.'], 403);
        }
        admin_header('Sin permiso', '');
        echo '<div class="empty-panel"><span class="empty-panel__icon"><i class="fa-solid fa-lock"></i></span><h2>Acceso restringido</h2>'
            . '<p>Tu rol no tiene permiso para ver este módulo. Pide a un administrador que te lo otorgue.</p>'
            . '<a class="btn btn--primary" href="dashboard.php"><i class="fa-solid fa-house"></i> Ir al inicio</a></div>';
        admin_footer();
        exit;
    }
}

function is_api_request(): bool
{
    return basename($_SERVER['SCRIPT_NAME'] ?? '') === 'api.php';
}

/** Registra una acción en la bitácora. */
function log_activity(string $module, string $perm, ?int $recordId = null): void
{
    $u = current_user();
    try {
        db_exec(
            'INSERT INTO activity_logs (user_id, module_id, permission_id, record_id)
             SELECT ?, m.id, p.id, ? FROM modules m, permissions p WHERE m.code = ? AND p.code = ?',
            [$u['id'] ?? null, $recordId, $module, $perm]
        );
    } catch (Throwable $e) {
        // La bitácora nunca debe impedir la operación principal
    }
}

/** Ruta del panel para un módulo. */
function module_url(string $code): string
{
    return match ($code) {
        'dashboard' => 'dashboard.php',
        'settings'  => 'settings.php',
        'media'     => 'media.php',
        default     => 'crud.php?e=' . rawurlencode($code),
    };
}

/** Menú lateral agrupado según los permisos del usuario. */
function admin_menu(): array
{
    $mods = db_all(
        'SELECT m.code, m.name, m.icon, g.name AS group_name FROM modules m
         JOIN module_groups g ON g.id = m.module_group_id
         WHERE m.is_active = 1 ORDER BY g.sort_order, g.id, m.sort_order, m.id'
    );
    $out = [];
    foreach ($mods as $m) {
        if (can($m['code'], 'view')) {
            $out[$m['group_name']][] = $m;
        }
    }
    return $out;
}

function unread_messages(): int
{
    return can('contact_messages') ? (int)db_value('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0') : 0;
}

function pending_bookings(): int
{
    return can('bookings') ? (int)db_value("SELECT COUNT(*) FROM bookings b JOIN booking_statuses s ON s.id = b.booking_status_id WHERE s.code = 'pending'") : 0;
}

/** Cabecera HTML común del panel. */
function admin_header(string $title, string $activeModule, array $opts = []): void
{
    $u = current_user();
    $logo = setting('logo');
    $favicon = setting('favicon', $logo);
    $siteName = setting('site_name', 'Somos Mascoteros');
    $badges = ['contact_messages' => unread_messages(), 'bookings' => pending_bookings()];
    ?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="robots" content="noindex, nofollow, noarchive">
  <title><?= e($title) ?> · Panel · <?= e($siteName) ?></title>
  <?php if ($favicon): ?><link rel="icon" href="<?= e(url_for($favicon)) ?>"><?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="<?= e(google_fonts_url()) ?>">
  <link rel="stylesheet" href="../assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="../assets/vendor/select2/select2.min.css">
  <link rel="stylesheet" href="../assets/vendor/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="../assets/vendor/quill/quill.snow.css">
  <link rel="stylesheet" href="../assets/css/ui.css?v=1">
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
  <?= theme_style_tag() ?>
</head>
<body class="admin">
<?php if ($u): ?>
<div class="shell">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar__brand">
      <?php if ($logo): ?><img src="<?= e(url_for($logo)) ?>" alt=""><?php endif; ?>
      <div><strong><?= e($siteName) ?></strong><small>Panel de administración</small></div>
      <button class="sidebar__close" data-sidebar-close aria-label="Cerrar menú"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="sidebar__search">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="search" class="input" id="menuSearch" placeholder="Buscar módulo…" autocomplete="off">
    </div>
    <nav class="sidebar__nav" id="sidebarNav">
      <?php foreach (admin_menu() as $group => $mods): ?>
        <div class="sidebar__group">
          <span class="sidebar__group-title"><?= e($group) ?></span>
          <?php foreach ($mods as $m): $n = $badges[$m['code']] ?? 0; ?>
            <a href="<?= e(module_url($m['code'])) ?>" class="sidebar__link<?= $m['code'] === $activeModule ? ' is-active' : '' ?>">
              <i class="<?= e($m['icon']) ?>"></i><span><?= e($m['name']) ?></span>
              <?php if ($n): ?><em class="sidebar__badge"><?= $n ?></em><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar__foot">
      <a href="../index.php" target="_blank" rel="noopener" class="sidebar__link"><i class="fa-solid fa-arrow-up-right-from-square"></i><span>Ver sitio público</span></a>
    </div>
  </aside>
  <div class="sidebar-backdrop" data-sidebar-close></div>

  <div class="main">
    <header class="topbar">
      <button class="topbar__burger" id="sidebarToggle" aria-label="Abrir menú"><i class="fa-solid fa-bars-staggered"></i></button>
      <div class="topbar__title">
        <h1><?= e($title) ?></h1>
        <?php if (!empty($opts['subtitle'])): ?><p><?= e($opts['subtitle']) ?></p><?php endif; ?>
      </div>
      <div class="topbar__actions">
        <a class="topbar__icon" href="../index.php" target="_blank" rel="noopener" title="Ver sitio"><i class="fa-solid fa-globe"></i></a>
        <div class="usermenu" id="userMenu">
          <button class="usermenu__btn" aria-haspopup="true" aria-expanded="false">
            <span class="avatar"><?= e(mb_strtoupper(mb_substr($u['full_name'], 0, 1))) ?></span>
            <span class="usermenu__who"><strong><?= e($u['full_name']) ?></strong><small><?= e($u['role_name']) ?></small></span>
            <i class="fa-solid fa-chevron-down"></i>
          </button>
          <div class="usermenu__drop">
            <a href="profile.php"><i class="fa-solid fa-user-gear"></i> Mi perfil y contraseña</a>
            <a href="../index.php" target="_blank" rel="noopener"><i class="fa-solid fa-globe"></i> Ver sitio público</a>
            <a href="logout.php" data-logout class="is-danger"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
          </div>
        </div>
      </div>
    </header>
    <main class="content">
<?php endif;
}

/** Pie HTML común del panel. */
function admin_footer(array $jsData = []): void
{
    $u = current_user();
    ?>
<?php if ($u): ?>
    </main>
  </div>
</div>
<?php endif; ?>
<div class="toast-stack" id="toastStack" aria-live="polite"></div>
<script>window.SM = <?= json_encode(['csrf' => csrf_token(), 'base' => '../'] + $jsData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<script src="../assets/vendor/jquery/jquery.min.js"></script>
<script src="../assets/vendor/select2/select2.min.js"></script>
<script src="../assets/vendor/flatpickr/flatpickr.min.js"></script>
<script src="../assets/vendor/flatpickr/l10n/es.js"></script>
<script src="../assets/vendor/quill/quill.js"></script>
<script src="../assets/js/ui.js?v=1"></script>
<script src="../assets/js/admin.js?v=1"></script>
</body>
</html>
<?php
}
