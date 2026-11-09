<?php /** Portada */
$buttons = array_values(array_filter($s['items'], fn($it) => trim((string)$it['link_url']) !== ''));
$badges  = array_values(array_filter($s['items'], fn($it) => trim((string)$it['link_url']) === ''));
?>
<section class="hero" id="<?= e($s['anchor']) ?>">
  <div class="hero__aurora" aria-hidden="true"><span></span><span></span><span></span></div>
  <div class="hero__grid-bg" aria-hidden="true"></div>
  <div class="container hero__inner">
    <div class="hero__copy" data-reveal>
      <?php if ($s['eyebrow']): ?><span class="eyebrow eyebrow--glass"><i class="fa-solid fa-star"></i> <?= e($s['eyebrow']) ?></span><?php endif; ?>
      <h1 class="hero__title"><?= e($s['title']) ?></h1>
      <?php if ($s['subtitle']): ?><p class="hero__sub"><?= e($s['subtitle']) ?></p><?php endif; ?>
      <?php if ($s['body']): ?><div class="rich hero__body"><?= sanitize_html($s['body']) ?></div><?php endif; ?>
      <?php if ($buttons): ?>
        <div class="hero__ctas">
          <?php foreach ($buttons as $k => $b): ?>
            <a href="<?= e(safe_link($b['link_url'])) ?>" class="btn <?= $k === 0 ? 'btn--accent' : 'btn--ghost-light' ?>"><?= icon($b['icon'], 'fa-solid fa-arrow-right') ?> <?= e($b['title']) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="hero__visual" data-reveal="right">
      <div class="hero__blob">
        <?php if ($s['image']): ?><img src="<?= e(url_for($s['image'])) ?>" alt="<?= e($s['image_alt'] ?: $s['title']) ?>"><?php endif; ?>
      </div>
      <div class="hero__orbit" aria-hidden="true"><i class="fa-solid fa-paw"></i></div>
      <?php foreach ($badges as $k => $b): ?>
        <div class="hero__badge hero__badge--<?= $k % 3 ?>">
          <span class="hero__badge-icon"><?= icon($b['icon']) ?></span>
          <span><?php if ($b['highlight']): ?><strong><?= e($b['highlight']) ?></strong> <?php endif; ?><?= e($b['title']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <a href="#main" class="hero__scroll" data-scroll-next aria-label="Bajar"><span></span></a>
</section>
