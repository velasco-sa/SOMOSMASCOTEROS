<?php /** Galería */
$cats = db_all('SELECT DISTINCT c.id, c.name, c.sort_order FROM gallery_categories c
                JOIN gallery_photos p ON p.gallery_category_id = c.id AND p.is_visible = 1
                ORDER BY c.sort_order, c.name');
$photos = db_all('SELECT p.caption, p.gallery_category_id AS cat, m.file_path, m.alt_text FROM gallery_photos p
                  JOIN media m ON m.id = p.media_id WHERE p.is_visible = 1 ORDER BY p.sort_order, p.id');
?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container">
    <?php section_head($s); ?>
    <?php if ($s['body']): ?><div class="rich rich--center" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
    <?php if (count($cats) > 1): ?>
      <div class="filters" role="tablist" data-reveal>
        <button class="filter is-active" data-filter="all" role="tab">Todas</button>
        <?php foreach ($cats as $c): ?><button class="filter" data-filter="<?= (int)$c['id'] ?>" role="tab"><?= e($c['name']) ?></button><?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="masonry" data-gallery>
      <?php foreach ($photos as $k => $p): ?>
        <figure class="masonry__item" data-cat="<?= (int)$p['cat'] ?>" data-reveal style="--d:<?= ($k % 4) * 60 ?>ms">
          <button type="button" class="masonry__btn" data-full="<?= e(url_for($p['file_path'])) ?>" data-caption="<?= e($p['caption']) ?>" aria-label="Ampliar imagen">
            <img src="<?= e(url_for($p['file_path'])) ?>" alt="<?= e($p['alt_text'] ?: $p['caption']) ?>" loading="lazy">
            <span class="masonry__zoom"><i class="fa-solid fa-magnifying-glass-plus"></i></span>
          </button>
          <?php if ($p['caption']): ?><figcaption><?= e($p['caption']) ?></figcaption><?php endif; ?>
        </figure>
      <?php endforeach; ?>
    </div>
    <?php if (!$photos): ?><div class="empty-state"><i class="fa-solid fa-images"></i><p>Muy pronto compartiremos fotos.</p></div><?php endif; ?>
  </div>
</section>
