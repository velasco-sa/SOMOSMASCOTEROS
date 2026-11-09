<?php
/**
 * Sitio público de Somos Mascoteros.
 * Todo el contenido proviene de la base de datos.
 */
declare(strict_types=1);
define('BASE_PREFIX', '');
require __DIR__ . '/includes/bootstrap.php';

// ---------- Página solicitada ----------
$slug = isset($_GET['p']) ? preg_replace('/[^a-z0-9\-]/', '', strtolower((string)$_GET['p'])) : '';
$page = $slug !== ''
    ? db_one('SELECT * FROM pages WHERE slug = ? AND is_published = 1', [$slug])
    : db_one('SELECT * FROM pages WHERE is_home = 1 AND is_published = 1 ORDER BY sort_order LIMIT 1');
if (!$page) {
    $page = db_one('SELECT * FROM pages WHERE is_published = 1 ORDER BY is_home DESC, sort_order LIMIT 1');
}
$notFound = $slug !== '' && (!$page || $page['slug'] !== $slug);
if ($notFound) {
    http_response_code(404);
}

$homePage = db_one('SELECT * FROM pages WHERE is_home = 1 AND is_published = 1 ORDER BY sort_order LIMIT 1');
$isHome = $page && $homePage && (int)$page['id'] === (int)$homePage['id'] && !$notFound;

// ---------- Secciones y elementos ----------
$sections = [];
if ($page && !$notFound) {
    $sections = db_all(
        'SELECT s.*, t.code AS type_code, m.file_path AS image, m.alt_text AS image_alt
         FROM sections s
         JOIN section_types t ON t.id = s.section_type_id
         LEFT JOIN media m ON m.id = s.media_id
         WHERE s.page_id = ? AND s.is_visible = 1
         ORDER BY s.sort_order, s.id',
        [$page['id']]
    );
    if ($sections) {
        $ids = array_column($sections, 'id');
        $in = implode(',', array_fill(0, count($ids), '?'));
        $items = db_all(
            "SELECT i.*, m.file_path AS image, m.alt_text AS image_alt FROM section_items i
             LEFT JOIN media m ON m.id = i.media_id
             WHERE i.section_id IN ($in) AND i.is_visible = 1 ORDER BY i.sort_order, i.id",
            $ids
        );
        $bySection = [];
        foreach ($items as $it) {
            $bySection[$it['section_id']][] = $it;
        }
        foreach ($sections as &$s) {
            $s['items'] = $bySection[$s['id']] ?? [];
        }
        unset($s);
    }
}

// ---------- Datos compartidos ----------
$contactChannels = db_all(
    'SELECT c.*, t.name AS type_name, t.icon, t.url_prefix FROM contact_channels c
     JOIN contact_types t ON t.id = c.contact_type_id WHERE c.is_visible = 1 ORDER BY c.sort_order, c.id'
);
$socialLinks = db_all(
    'SELECT l.url, p.name, p.icon FROM social_links l JOIN social_platforms p ON p.id = l.social_platform_id
     WHERE l.is_visible = 1 ORDER BY l.sort_order, l.id'
);

/** Construye el enlace de un medio de contacto. */
function channel_href(array $c): string
{
    $v = trim((string)$c['value']);
    $prefix = (string)($c['url_prefix'] ?? '');
    if ($prefix === '') {
        return preg_match('~^https?://~i', $v) ? $v : '#';
    }
    if (str_starts_with($prefix, 'tel:') || str_contains($prefix, 'wa.me')) {
        $v = preg_replace('/[^0-9+]/', '', $v);
        if (str_contains($prefix, 'wa.me')) {
            $v = ltrim($v, '+');
        }
    }
    return $prefix . $v;
}

$whatsapp = null;
foreach ($contactChannels as $c) {
    if (str_contains((string)$c['url_prefix'], 'wa.me')) {
        $whatsapp = $c;
        break;
    }
}

// Menú: anclas de la página de inicio + otras páginas
$menu = [];
if ($homePage) {
    $anchors = db_all(
        'SELECT anchor, COALESCE(NULLIF(menu_label, \'\'), title) AS label FROM sections
         WHERE page_id = ? AND is_visible = 1 AND show_in_menu = 1 ORDER BY sort_order, id',
        [$homePage['id']]
    );
    foreach ($anchors as $a) {
        $menu[] = ['label' => $a['label'], 'href' => ($isHome ? '' : 'index.php') . '#' . $a['anchor'], 'anchor' => $a['anchor']];
    }
}
$extraPages = db_all('SELECT slug, title FROM pages WHERE is_published = 1 AND show_in_menu = 1 AND is_home = 0 ORDER BY sort_order, id');
foreach ($extraPages as $p) {
    $menu[] = ['label' => $p['title'], 'href' => 'index.php?p=' . rawurlencode($p['slug']), 'active' => $page && $p['slug'] === $page['slug']];
}
$footerPages = db_all('SELECT slug, title FROM pages WHERE is_published = 1 AND is_home = 0 ORDER BY sort_order, id');

$siteName = setting('site_name', 'Somos Mascoteros');
$logo = setting('logo');
$favicon = setting('favicon', $logo);
$pageTitle = $notFound ? 'Página no encontrada · ' . $siteName
    : ($isHome ? setting('meta_title', $siteName) : ($page['title'] ?? '') . ' · ' . $siteName);
$metaDesc = ($page && !$isHome && $page['meta_description']) ? $page['meta_description'] : setting('meta_description');

/** Imprime el encabezado estándar de una sección. */
function section_head(array $s, string $align = 'center'): void
{
    if (!$s['eyebrow'] && !$s['title'] && !$s['subtitle']) {
        return;
    }
    echo '<header class="sec-head sec-head--' . e($align) . '" data-reveal>';
    if ($s['eyebrow']) {
        echo '<span class="eyebrow"><i class="fa-solid fa-paw"></i> ' . e($s['eyebrow']) . '</span>';
    }
    if ($s['title']) {
        echo '<h2 class="sec-title">' . e($s['title']) . '</h2>';
    }
    if ($s['subtitle']) {
        echo '<p class="sec-sub">' . e($s['subtitle']) . '</p>';
    }
    echo '</header>';
}

/** Ícono seguro (solo clases de Font Awesome). */
function icon(?string $cls, string $fallback = 'fa-solid fa-paw'): string
{
    $cls = trim((string)$cls);
    if ($cls === '' || !preg_match('/^[a-z0-9\- ]+$/', $cls)) {
        $cls = $fallback;
    }
    return '<i class="' . e($cls) . '" aria-hidden="true"></i>';
}

/** Enlace seguro para elementos. */
function safe_link(?string $url): string
{
    $url = trim((string)$url);
    if ($url === '' || preg_match('~^\s*(javascript|data):~i', $url)) {
        return '#';
    }
    return $url;
}
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($metaDesc) ?>">
  <meta name="keywords" content="<?= e(setting('meta_keywords')) ?>">
  <meta name="theme-color" content="<?= e(safe_color(setting('color_primary'), '#1C3762')) ?>">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($metaDesc) ?>">
  <?php if ($logo): ?><meta property="og:image" content="<?= e(url_for($logo)) ?>"><?php endif; ?>
  <?php if ($favicon): ?><link rel="icon" href="<?= e(url_for($favicon)) ?>"><link rel="apple-touch-icon" href="<?= e(url_for($favicon)) ?>"><?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="<?= e(google_fonts_url()) ?>">
  <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="assets/vendor/select2/select2.min.css">
  <link rel="stylesheet" href="assets/vendor/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="assets/css/ui.css?v=1">
  <link rel="stylesheet" href="assets/css/site.css?v=1">
  <?= theme_style_tag() ?>
</head>
<body class="<?= $isHome ? 'is-home' : 'is-inner' ?>">

<!-- Pantalla de carga -->
<div class="preloader" id="preloader" aria-hidden="true">
  <div class="preloader__ring"></div>
  <?php if ($logo): ?><img src="<?= e(url_for($logo)) ?>" alt="" class="preloader__logo"><?php endif; ?>
</div>

<div class="scroll-progress" id="scrollProgress"></div>

<!-- Encabezado -->
<header class="nav" id="nav">
  <div class="nav__inner container">
    <a href="<?= $isHome ? '#' . e($sections[0]['anchor'] ?? '') : 'index.php' ?>" class="brand" aria-label="<?= e($siteName) ?>">
      <?php if ($logo): ?><img src="<?= e(url_for($logo)) ?>" alt="<?= e($siteName) ?>" class="brand__logo"><?php endif; ?>
      <?php if (setting('show_name_in_header', '1') === '1'): ?>
        <span class="brand__text"><strong><?= e($siteName) ?></strong><small><?= e(setting('site_tagline')) ?></small></span>
      <?php endif; ?>
    </a>
    <nav class="nav__links" id="navLinks" aria-label="Menú principal">
      <?php foreach ($menu as $m): ?>
        <a href="<?= e($m['href']) ?>" class="nav__link<?= !empty($m['active']) ? ' is-active' : '' ?>" <?= isset($m['anchor']) ? 'data-anchor="' . e($m['anchor']) . '"' : '' ?>><?= e($m['label']) ?></a>
      <?php endforeach; ?>
      <div class="nav__mobile-extra">
        <?php foreach ($socialLinks as $sl): ?>
          <a href="<?= e(safe_link($sl['url'])) ?>" target="_blank" rel="noopener" aria-label="<?= e($sl['name']) ?>"><?= icon($sl['icon']) ?></a>
        <?php endforeach; ?>
      </div>
    </nav>
    <div class="nav__actions">
      <?php if ($whatsapp && setting('nav_cta_label') !== ''): ?>
        <a class="btn btn--accent btn--sm nav__cta" href="<?= e(channel_href($whatsapp)) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i><span><?= e(setting('nav_cta_label')) ?></span></a>
      <?php endif; ?>
      <button class="burger" id="burger" aria-label="Abrir menú" aria-expanded="false" aria-controls="navLinks"><span></span><span></span><span></span></button>
    </div>
  </div>
</header>

<main id="main">
<?php if ($notFound): ?>
  <section class="section notfound">
    <div class="container notfound__box" data-reveal>
      <div class="notfound__code">4<i class="fa-solid fa-paw"></i>4</div>
      <h1 class="sec-title">Esta página se fue de paseo</h1>
      <p class="sec-sub">No encontramos lo que buscabas, pero podemos llevarte de regreso.</p>
      <a class="btn btn--primary" href="index.php"><i class="fa-solid fa-house"></i> Volver al inicio</a>
    </div>
  </section>
<?php else: ?>
  <?php if (!$isHome && $page): ?>
    <section class="page-hero">
      <div class="page-hero__bg"></div>
      <div class="container" data-reveal>
        <nav class="crumbs" aria-label="Ruta"><a href="index.php"><i class="fa-solid fa-house"></i> Inicio</a><i class="fa-solid fa-chevron-right"></i><span><?= e($page['title']) ?></span></nav>
        <h1 class="page-hero__title"><?= e($page['title']) ?></h1>
        <?php if ($page['meta_description']): ?><p class="page-hero__sub"><?= e($page['meta_description']) ?></p><?php endif; ?>
      </div>
    </section>
  <?php endif; ?>
  <?php
  $i = 0;
  foreach ($sections as $s) {
      $file = __DIR__ . '/includes/sections/' . basename($s['type_code']) . '.php';
      if (!is_file($file)) {
          $file = __DIR__ . '/includes/sections/text.php';
      }
      $alt = $i % 2 === 1;
      include $file;
      $i++;
  }
  if (!$sections) {
      echo '<section class="section"><div class="container empty-state" data-reveal><i class="fa-solid fa-paw"></i><p>Esta página aún no tiene contenido.</p></div></section>';
  }
  ?>
<?php endif; ?>
</main>

<!-- Pie de página -->
<footer class="footer">
  <div class="footer__wave" aria-hidden="true">
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none"><path d="M0,64 C240,120 480,0 720,48 C960,96 1200,16 1440,56 L1440,120 L0,120 Z"></path></svg>
  </div>
  <div class="footer__body">
    <div class="container">
      <?php if (setting('footer_cta')): ?>
        <div class="footer__cta" data-reveal>
          <h3><?= e(setting('footer_cta')) ?></h3>
          <?php if (setting('footer_cta_button') !== ''):
            $fl = safe_link(setting('footer_cta_link', '#disponibilidad'));
            if (!$isHome && str_starts_with($fl, '#')) { $fl = 'index.php' . $fl; } ?>
            <a class="btn btn--accent" href="<?= e($fl) ?>"><i class="fa-solid fa-calendar-check"></i> <?= e(setting('footer_cta_button')) ?></a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <div class="footer__grid">
        <div class="footer__brand">
          <?php if ($logo): ?><img src="<?= e(url_for($logo)) ?>" alt="<?= e($siteName) ?>" class="footer__logo"><?php endif; ?>
          <strong><?= e($siteName) ?></strong>
          <p><?= e(setting('footer_about')) ?></p>
          <div class="socials">
            <?php foreach ($socialLinks as $sl): ?>
              <a href="<?= e(safe_link($sl['url'])) ?>" target="_blank" rel="noopener" aria-label="<?= e($sl['name']) ?>"><?= icon($sl['icon']) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <div>
          <h4>Explora</h4>
          <ul class="footer__list">
            <?php foreach ($menu as $m): ?><li><a href="<?= e($m['href']) ?>"><?= e($m['label']) ?></a></li><?php endforeach; ?>
          </ul>
        </div>
        <?php if ($footerPages): ?>
        <div>
          <h4>Información</h4>
          <ul class="footer__list">
            <?php foreach ($footerPages as $fp): ?><li><a href="index.php?p=<?= e(rawurlencode($fp['slug'])) ?>"><?= e($fp['title']) ?></a></li><?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
        <div>
          <h4>Contacto</h4>
          <ul class="footer__list footer__list--icons">
            <?php foreach ($contactChannels as $c): ?>
              <li><a href="<?= e(channel_href($c)) ?>" <?= str_starts_with(channel_href($c), 'http') ? 'target="_blank" rel="noopener"' : '' ?>><?= icon($c['icon']) ?> <?= e($c['value']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
      <div class="footer__bottom">
        <span>© <?= date('Y') ?> <?= e(setting('footer_copyright', $siteName)) ?></span>
        <a href="#main" class="to-top" aria-label="Volver arriba"><i class="fa-solid fa-arrow-up"></i></a>
      </div>
    </div>
  </div>
</footer>

<?php if ($whatsapp && setting('whatsapp_float', '1') === '1'): ?>
  <a class="wa-float" href="<?= e(channel_href($whatsapp)) ?>?text=<?= e(rawurlencode(setting('whatsapp_float_message'))) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
    <i class="fa-brands fa-whatsapp"></i>
  </a>
<?php endif; ?>

<!-- Visor de imágenes -->
<div class="lightbox" id="lightbox" aria-hidden="true" role="dialog" aria-modal="true">
  <button class="lightbox__btn lightbox__close" data-lb="close" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
  <button class="lightbox__btn lightbox__prev" data-lb="prev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
  <figure class="lightbox__figure"><img src="" alt=""><figcaption></figcaption></figure>
  <button class="lightbox__btn lightbox__next" data-lb="next" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>
</div>

<div class="toast-stack" id="toastStack" aria-live="polite"></div>

<script>window.SM = { csrf: <?= json_encode(csrf_token()) ?> };</script>
<script src="assets/vendor/jquery/jquery.min.js"></script>
<script src="assets/vendor/select2/select2.min.js"></script>
<script src="assets/vendor/flatpickr/flatpickr.min.js"></script>
<script src="assets/vendor/flatpickr/l10n/es.js"></script>
<script src="assets/js/ui.js?v=1"></script>
<script src="assets/js/site.js?v=1"></script>
</body>
</html>
