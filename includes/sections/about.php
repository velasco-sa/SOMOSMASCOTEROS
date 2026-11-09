<?php /** Quiénes somos */ ?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container split">
    <div class="split__media" data-reveal="left">
      <div class="frame">
        <?php if ($s['image']): ?><img src="<?= e(url_for($s['image'])) ?>" alt="<?= e($s['image_alt'] ?: $s['title']) ?>" loading="lazy"><?php endif; ?>
      </div>
      <div class="frame__deco" aria-hidden="true"></div>
    </div>
    <div class="split__copy">
      <?php section_head($s, 'left'); ?>
      <?php if ($s['body']): ?><div class="rich" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
      <?php if ($s['items']): ?>
        <div class="stats" data-reveal>
          <?php foreach ($s['items'] as $it): ?>
            <div class="stat">
              <span class="stat__icon"><?= icon($it['icon']) ?></span>
              <strong class="stat__value" data-count="<?= e($it['highlight']) ?>"><?= e($it['highlight']) ?></strong>
              <span class="stat__label"><?= e($it['title']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
