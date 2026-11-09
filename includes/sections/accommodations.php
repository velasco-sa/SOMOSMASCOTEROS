<?php /** Dónde duermen (alojamientos registrados) */
$rooms = db_all('SELECT a.*, p.name AS pet, p.icon AS pet_icon, m.file_path AS image, m.alt_text AS image_alt
                 FROM accommodations a JOIN pet_types p ON p.id = a.pet_type_id
                 LEFT JOIN media m ON m.id = a.media_id
                 WHERE a.is_active = 1 AND p.is_active = 1 ORDER BY a.sort_order, a.id');
?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container">
    <?php section_head($s); ?>
    <?php if ($s['body']): ?><div class="rich rich--center" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
    <div class="rooms">
      <?php foreach ($rooms as $k => $r): ?>
        <article class="room" data-reveal style="--d:<?= ($k % 3) * 90 ?>ms">
          <div class="room__media">
            <?php if ($r['image']): ?><img src="<?= e(url_for($r['image'])) ?>" alt="<?= e($r['image_alt'] ?: $r['name']) ?>" loading="lazy"><?php endif; ?>
            <span class="room__pet"><?= icon($r['pet_icon']) ?> <?= e($r['pet']) ?></span>
          </div>
          <div class="room__body">
            <h3><?= e($r['name']) ?></h3>
            <?php if ($r['description']): ?><p><?= nl2br(e($r['description'])) ?></p><?php endif; ?>
            <span class="room__cap"><i class="fa-solid fa-bed"></i> <?= (int)$r['capacity'] ?> <?= (int)$r['capacity'] === 1 ? 'lugar' : 'lugares' ?></span>
          </div>
        </article>
      <?php endforeach; ?>
      <?php if (!$rooms): ?><div class="empty-state"><i class="fa-solid fa-bed"></i><p>Pronto mostraremos nuestras instalaciones.</p></div><?php endif; ?>
    </div>
  </div>
</section>
