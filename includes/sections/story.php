<?php /** Historia / línea de tiempo */ ?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container split split--reverse">
    <div class="split__copy">
      <?php section_head($s, 'left'); ?>
      <?php if ($s['body']): ?><div class="rich" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
    </div>
    <div class="split__media" data-reveal="right">
      <div class="frame frame--tilt">
        <?php if ($s['image']): ?><img src="<?= e(url_for($s['image'])) ?>" alt="<?= e($s['image_alt'] ?: $s['title']) ?>" loading="lazy"><?php endif; ?>
      </div>
    </div>
  </div>
  <?php if ($s['items']): ?>
    <div class="container">
      <ol class="timeline">
        <?php foreach ($s['items'] as $it): ?>
          <li class="timeline__item" data-reveal>
            <span class="timeline__dot"><?= icon($it['icon']) ?></span>
            <div class="timeline__card glass">
              <?php if ($it['highlight']): ?><span class="timeline__year"><?= e($it['highlight']) ?></span><?php endif; ?>
              <h3><?= e($it['title']) ?></h3>
              <?php if ($it['description']): ?><p><?= nl2br(e($it['description'])) ?></p><?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>
  <?php endif; ?>
</section>
