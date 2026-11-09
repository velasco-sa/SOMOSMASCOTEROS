<?php /** Texto libre */ ?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container <?= $s['image'] ? 'split' : 'container--narrow' ?>">
    <?php if ($s['image']): ?>
      <div class="split__media" data-reveal="left"><div class="frame"><img src="<?= e(url_for($s['image'])) ?>" alt="<?= e($s['image_alt'] ?: $s['title']) ?>" loading="lazy"></div></div>
    <?php endif; ?>
    <div class="<?= $s['image'] ? 'split__copy' : '' ?>">
      <?php section_head($s, $s['image'] ? 'left' : 'center'); ?>
      <?php if ($s['body']): ?><div class="rich rich--card glass" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
      <?php if ($s['items']): ?>
        <div class="text-items" data-reveal>
          <?php foreach ($s['items'] as $it): ?>
            <div class="care"><span class="care__icon"><?= icon($it['icon']) ?></span><div><h3><?= e($it['title']) ?></h3><?php if ($it['description']): ?><p><?= nl2br(e($it['description'])) ?></p><?php endif; ?></div></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
