<?php /** Llamado a la acción */ ?>
<section class="section section--tight" id="<?= e($s['anchor']) ?>">
  <div class="container">
    <div class="cta" data-reveal>
      <div class="cta__glow" aria-hidden="true"></div>
      <div class="cta__copy">
        <?php if ($s['eyebrow']): ?><span class="eyebrow eyebrow--glass"><?= e($s['eyebrow']) ?></span><?php endif; ?>
        <?php if ($s['title']): ?><h2><?= e($s['title']) ?></h2><?php endif; ?>
        <?php if ($s['subtitle']): ?><p><?= e($s['subtitle']) ?></p><?php endif; ?>
        <?php if ($s['body']): ?><div class="rich rich--light"><?= sanitize_html($s['body']) ?></div><?php endif; ?>
      </div>
      <div class="cta__actions">
        <?php foreach ($s['items'] as $k => $b): ?>
          <a href="<?= e(safe_link($b['link_url'])) ?>" class="btn <?= $k === 0 ? 'btn--accent' : 'btn--ghost-light' ?>"><?= icon($b['icon'], 'fa-solid fa-arrow-right') ?> <?= e($b['title']) ?></a>
        <?php endforeach; ?>
      </div>
      <?php if ($s['image']): ?><img class="cta__img" src="<?= e(url_for($s['image'])) ?>" alt="" loading="lazy"><?php endif; ?>
    </div>
  </div>
</section>
