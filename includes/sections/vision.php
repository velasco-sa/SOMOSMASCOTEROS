<?php /** Misión, visión y metas */ ?>
<section class="section section--dark" id="<?= e($s['anchor']) ?>">
  <div class="section__glow" aria-hidden="true"></div>
  <div class="container">
    <?php section_head($s); ?>
    <?php if ($s['body']): ?><div class="rich rich--center rich--light" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
    <div class="vision-grid">
      <?php foreach ($s['items'] as $k => $it): ?>
        <article class="vision-card" data-reveal style="--d:<?= $k * 80 ?>ms">
          <span class="vision-card__num"><?= str_pad((string)($k + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <span class="vision-card__icon"><?= icon($it['icon']) ?></span>
          <h3><?= e($it['title']) ?></h3>
          <?php if ($it['description']): ?><p><?= nl2br(e($it['description'])) ?></p><?php endif; ?>
          <?php if ($it['highlight']): ?><span class="chip chip--accent"><?= e($it['highlight']) ?></span><?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
    <?php if ($s['image']): ?>
      <div class="vision-image" data-reveal><img src="<?= e(url_for($s['image'])) ?>" alt="<?= e($s['image_alt'] ?: $s['title']) ?>" loading="lazy"></div>
    <?php endif; ?>
  </div>
</section>
