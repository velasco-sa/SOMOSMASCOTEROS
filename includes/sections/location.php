<?php /** Ubicación */
$locations = db_all('SELECT * FROM locations WHERE is_visible = 1 ORDER BY sort_order, id');
$hours = db_all('SELECT h.*, w.name AS day FROM business_hours h JOIN weekdays w ON w.id = h.weekday_id ORDER BY h.location_id, w.id');
$hoursBy = [];
foreach ($hours as $h) { $hoursBy[$h['location_id']][] = $h; }
$today = (int)date('N');
?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container">
    <?php section_head($s); ?>
    <?php if ($s['body']): ?><div class="rich rich--center" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
    <?php foreach ($locations as $loc):
      $lat = $loc['latitude'] !== null ? (float)$loc['latitude'] : null;
      $lng = $loc['longitude'] !== null ? (float)$loc['longitude'] : null;
      $full = trim($loc['address'] . ', ' . $loc['city'] . ($loc['state'] ? ', ' . $loc['state'] : '') . ($loc['postal_code'] ? ' ' . $loc['postal_code'] : '') . ', ' . $loc['country']);
      $dirs = $lat !== null && $lng !== null
        ? 'https://www.google.com/maps/dir/?api=1&destination=' . $lat . ',' . $lng
        : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($full);
    ?>
      <div class="location" data-reveal>
        <div class="location__map">
          <?php if ($lat !== null && $lng !== null):
            $d = 0.006; $bbox = ($lng - $d) . ',' . ($lat - $d) . ',' . ($lng + $d) . ',' . ($lat + $d); ?>
            <iframe title="Mapa de <?= e($loc['name']) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
              src="https://www.openstreetmap.org/export/embed.html?bbox=<?= e(rawurlencode($bbox)) ?>&amp;layer=mapnik&amp;marker=<?= e($lat . ',' . $lng) ?>"></iframe>
          <?php else: ?>
            <div class="location__nomap"><i class="fa-solid fa-map-location-dot"></i></div>
          <?php endif; ?>
        </div>
        <div class="location__card glass-strong">
          <h3><i class="fa-solid fa-location-dot"></i> <?= e($loc['name']) ?></h3>
          <p class="location__addr"><?= e($full) ?></p>
          <?php if ($loc['reference_notes']): ?><p class="location__ref"><i class="fa-solid fa-signs-post"></i> <?= e($loc['reference_notes']) ?></p><?php endif; ?>
          <?php if (!empty($hoursBy[$loc['id']])): ?>
            <ul class="hours">
              <?php foreach ($hoursBy[$loc['id']] as $h): ?>
                <li class="<?= (int)$h['weekday_id'] === $today ? 'is-today' : '' ?>">
                  <span><?= e($h['day']) ?></span>
                  <span><?= $h['is_closed'] || !$h['opens_at'] ? '<em>Cerrado</em>' : e(substr($h['opens_at'], 0, 5) . ' – ' . substr((string)$h['closes_at'], 0, 5)) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <a class="btn btn--primary btn--block" href="<?= e($dirs) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-diamond-turn-right"></i> Cómo llegar</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
