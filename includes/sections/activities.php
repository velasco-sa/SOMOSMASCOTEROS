<?php /** Actividades */ ?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container">
    <?php section_head($s); ?>
    <?php if ($s['body']): ?><div class="rich rich--center" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
    <div class="activities">
      <?php foreach ($s['items'] as $k => $it): ?>
        <article class="activity" data-reveal style="--d:<?= ($k % 4) * 80 ?>ms">
          <div class="activity__media">
            <?php if ($it['image']): ?><img src="<?= e(url_for($it['image'])) ?>" alt="<?= e($it['image_alt'] ?: $it['title']) ?>" loading="lazy">
            <?php else: ?><div class="activity__ph"><?= icon($it['icon']) ?></div><?php endif; ?>
          </div>
          <div class="activity__body">
            <span class="activity__icon"><?= icon($it['icon']) ?></span>
            <h3><?= e($it['title']) ?></h3>
            <?php if ($it['description']): ?><p><?= nl2br(e($it['description'])) ?></p><?php endif; ?>
            <?php if ($it['highlight']): ?><span class="activity__time"><i class="fa-regular fa-clock"></i> <?= e($it['highlight']) ?></span><?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
