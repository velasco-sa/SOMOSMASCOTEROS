<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
$u = require_login();
require_perm('dashboard');

$hour = (int)date('G');
$greet = $hour < 12 ? 'Buenos días' : ($hour < 19 ? 'Buenas tardes' : 'Buenas noches');
$today = date('Y-m-d');

$cards = [];
if (can('bookings')) {
    $cards[] = ['Reservas activas hoy', (int)db_value('SELECT COALESCE(SUM(b.pets_count),0) FROM bookings b JOIN booking_statuses s ON s.id=b.booking_status_id WHERE s.blocks_capacity=1 AND b.check_in <= ? AND b.check_out > ?', [$today, $today]), 'fa-solid fa-dog', 'crud.php?e=bookings', 'Mascotas hospedadas'];
    $cards[] = ['Solicitudes pendientes', pending_bookings(), 'fa-solid fa-hourglass-half', 'crud.php?e=bookings', 'Por confirmar'];
}
if (can('contact_messages')) {
    $cards[] = ['Mensajes sin leer', unread_messages(), 'fa-solid fa-envelope', 'crud.php?e=contact_messages', 'Formulario de contacto'];
}
if (can('gallery_photos')) {
    $cards[] = ['Fotos en galería', (int)db_value('SELECT COUNT(*) FROM gallery_photos WHERE is_visible = 1'), 'fa-solid fa-images', 'crud.php?e=gallery_photos', 'Visibles en el sitio'];
}
if (can('pages')) {
    $cards[] = ['Páginas publicadas', (int)db_value('SELECT COUNT(*) FROM pages WHERE is_published = 1'), 'fa-solid fa-file-lines', 'crud.php?e=pages', (int)db_value('SELECT COUNT(*) FROM sections WHERE is_visible = 1') . ' secciones visibles'];
}

// Ocupación de los próximos 14 días por tipo de mascota
$occupancy = [];
if (can('bookings')) {
    $types = db_all('SELECT id, name, icon FROM pet_types WHERE is_active = 1 ORDER BY sort_order');
    $end = date('Y-m-d', strtotime('+14 day'));
    foreach ($types as $t) {
        $r = compute_availability((int)$t['id'], $today, $end, 1);
        $occupancy[] = ['type' => $t, 'days' => array_values(array_filter($r['days'], fn($d) => $d['night'])), 'capacity' => $r['capacity']];
    }
    $upcoming = db_all(
        'SELECT b.id, b.pet_name, b.pets_count, b.check_in, b.check_out, c.full_name, p.name AS pet_type, p.icon, s.name AS status, s.color
         FROM bookings b JOIN customers c ON c.id = b.customer_id JOIN pet_types p ON p.id = b.pet_type_id
         JOIN booking_statuses s ON s.id = b.booking_status_id
         WHERE b.check_out >= ? AND s.code <> \'cancelled\' ORDER BY b.check_in LIMIT 6',
        [$today]
    );
}
$messages = can('contact_messages') ? db_all('SELECT id, full_name, subject, message, is_read, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5') : [];
$activity = can('activity_logs') ? db_all(
    'SELECT a.created_at, a.record_id, u.full_name, m.name AS module, m.icon, p.code AS action, p.name AS action_name
     FROM activity_logs a LEFT JOIN users u ON u.id = a.user_id JOIN modules m ON m.id = a.module_id JOIN permissions p ON p.id = a.permission_id
     ORDER BY a.created_at DESC, a.id DESC LIMIT 6'
) : [];

$quick = array_values(array_filter([
    can('sections', 'edit') ? ['Editar textos', 'fa-solid fa-pen-to-square', 'crud.php?e=sections'] : null,
    can('gallery_photos', 'create') ? ['Subir fotos', 'fa-solid fa-camera', 'crud.php?e=gallery_photos'] : null,
    can('bookings', 'create') ? ['Nueva reserva', 'fa-solid fa-calendar-plus', 'crud.php?e=bookings&new=1'] : null,
    can('settings', 'edit') ? ['Logo y colores', 'fa-solid fa-palette', 'settings.php'] : null,
    can('pages', 'create') ? ['Nueva página', 'fa-solid fa-file-circle-plus', 'crud.php?e=pages&new=1'] : null,
    can('users', 'create') ? ['Nuevo usuario', 'fa-solid fa-user-plus', 'crud.php?e=users&new=1'] : null,
]));

function fdate(string $d, bool $time = false): string
{
    $m = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
    $ts = strtotime($d);
    return date('j', $ts) . ' ' . $m[(int)date('n', $ts) - 1] . ($time ? ' · ' . date('H:i', $ts) : '');
}

admin_header('Panel principal', 'dashboard', ['subtitle' => $greet . ', ' . explode(' ', $u['full_name'])[0] . ' 👋']);
?>
<section class="welcome">
  <div class="welcome__copy">
    <span class="chip chip--accent"><i class="fa-regular fa-calendar"></i> <?= e(fdate($today)) ?> <?= date('Y') ?></span>
    <h2><?= e($greet) ?>, <?= e(explode(' ', $u['full_name'])[0]) ?></h2>
    <p>Desde aquí puedes administrar todo el contenido del sitio. Usa el menú lateral o los accesos rápidos.</p>
  </div>
  <?php if ($quick): ?>
  <div class="quick">
    <?php foreach ($quick as $q): ?><a href="<?= e($q[2]) ?>" class="quick__item"><i class="<?= e($q[1]) ?>"></i><span><?= e($q[0]) ?></span></a><?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>

<?php if ($cards): ?>
<section class="kpis">
  <?php foreach ($cards as $k => $c): ?>
    <a href="<?= e($c[3]) ?>" class="kpi" style="--d:<?= $k * 60 ?>ms">
      <span class="kpi__icon"><i class="<?= e($c[2]) ?>"></i></span>
      <span class="kpi__label"><?= e($c[0]) ?></span>
      <strong class="kpi__value" data-count="<?= (int)$c[1] ?>"><?= (int)$c[1] ?></strong>
      <small class="kpi__hint"><?= e($c[4]) ?></small>
    </a>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<div class="dash-grid">
  <?php if ($occupancy): ?>
  <section class="card card--span2">
    <header class="card__head"><h3><i class="fa-solid fa-chart-column"></i> Ocupación · próximos 14 días</h3><a class="btn btn--ghost btn--xs" href="crud.php?e=accommodations">Capacidad</a></header>
    <?php foreach ($occupancy as $o): ?>
      <div class="occ">
        <div class="occ__title"><span class="s2-ico"><i class="<?= e($o['type']['icon']) ?>"></i></span><strong><?= e($o['type']['name']) ?></strong><small><?= (int)$o['capacity'] ?> lugares</small></div>
        <div class="occ__bars">
          <?php foreach ($o['days'] as $d):
            $pct = $d['capacity'] ? min(100, round($d['occupied'] / $d['capacity'] * 100)) : 0;
            $cls = $d['closed'] ? 'is-closed' : ($pct >= 100 ? 'is-full' : ($pct >= 75 ? 'is-high' : '')); ?>
            <div class="occ__bar <?= $cls ?>" title="<?= e(fdate($d['date'])) ?>: <?= $d['closed'] ? 'Cerrado' : $d['occupied'] . ' / ' . $d['capacity'] ?>">
              <span style="--h:<?= $d['closed'] ? 100 : max(4, $pct) ?>%"></span>
              <small><?= date('j', strtotime($d['date'])) ?></small>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="occ__legend"><span><i class="dot"></i> Ocupación</span><span><i class="dot dot--high"></i> +75%</span><span><i class="dot dot--full"></i> Lleno</span><span><i class="dot dot--closed"></i> Cerrado</span></div>
  </section>
  <?php endif; ?>

  <?php if (!empty($upcoming) || can('bookings')): ?>
  <section class="card">
    <header class="card__head"><h3><i class="fa-solid fa-calendar-check"></i> Próximas estancias</h3><a class="btn btn--ghost btn--xs" href="crud.php?e=bookings">Ver todas</a></header>
    <ul class="mini-list">
      <?php foreach ($upcoming ?? [] as $b): ?>
        <li>
          <span class="mini-list__icon" style="background:<?= e(safe_color($b['color'], '#9ACCCD')) ?>33;color:<?= e(safe_color($b['color'], '#1C3762')) ?>"><i class="<?= e($b['icon']) ?>"></i></span>
          <div><strong><?= e($b['pet_name']) ?><?= (int)$b['pets_count'] > 1 ? ' (+' . ((int)$b['pets_count'] - 1) . ')' : '' ?></strong><small><?= e($b['full_name']) ?> · <?= e(fdate($b['check_in'])) ?> → <?= e(fdate($b['check_out'])) ?></small></div>
          <span class="chip" style="background:<?= e(safe_color($b['color'], '#9ACCCD')) ?>33"><?= e($b['status']) ?></span>
        </li>
      <?php endforeach; ?>
      <?php if (empty($upcoming)): ?><li class="mini-list__empty"><i class="fa-solid fa-mug-hot"></i> No hay estancias próximas.</li><?php endif; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if (can('contact_messages')): ?>
  <section class="card">
    <header class="card__head"><h3><i class="fa-solid fa-envelope-open-text"></i> Mensajes recientes</h3><a class="btn btn--ghost btn--xs" href="crud.php?e=contact_messages">Ver todos</a></header>
    <ul class="mini-list">
      <?php foreach ($messages as $m): ?>
        <li class="<?= $m['is_read'] ? '' : 'is-unread' ?>">
          <span class="avatar avatar--sm"><?= e(mb_strtoupper(mb_substr($m['full_name'], 0, 1))) ?></span>
          <div><strong><?= e($m['full_name']) ?></strong><small><?= e($m['subject'] ?: excerpt($m['message'], 60)) ?></small></div>
          <small class="mini-list__time"><?= e(fdate($m['created_at'])) ?></small>
        </li>
      <?php endforeach; ?>
      <?php if (!$messages): ?><li class="mini-list__empty"><i class="fa-solid fa-inbox"></i> Sin mensajes todavía.</li><?php endif; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if ($activity): ?>
  <section class="card">
    <header class="card__head"><h3><i class="fa-solid fa-clock-rotate-left"></i> Actividad reciente</h3><a class="btn btn--ghost btn--xs" href="crud.php?e=activity_logs">Bitácora</a></header>
    <ul class="mini-list">
      <?php foreach ($activity as $a):
        $col = ['create' => 'chip--success', 'edit' => '', 'delete' => 'chip--danger'][$a['action']] ?? ''; ?>
        <li>
          <span class="mini-list__icon"><i class="<?= e($a['icon']) ?>"></i></span>
          <div><strong><?= e($a['full_name'] ?? 'Usuario eliminado') ?></strong><small><?= e($a['module']) ?><?= $a['record_id'] ? ' · #' . (int)$a['record_id'] : '' ?></small></div>
          <span class="chip <?= $col ?>"><?= e($a['action_name']) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>
</div>
<?php admin_footer(['page' => 'dashboard']); ?>
