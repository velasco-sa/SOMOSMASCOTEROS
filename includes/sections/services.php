<?php /** Servicios */ ?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container">
    <?php section_head($s); ?>
    <?php if ($s['body']): ?><div class="rich rich--center" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
    <div class="bento">
      <?php foreach ($s['items'] as $k => $it): ?>
        <article class="service<?= $k === 0 ? ' service--featured' : '' ?>" data-reveal style="--d:<?= ($k % 3) * 90 ?>ms">
          <?php if ($it['image']): ?><img class="service__img" src="<?= e(url_for($it['image'])) ?>" alt="<?= e($it['image_alt'] ?: $it['title']) ?>" loading="lazy"><?php endif; ?>
          <span class="service__icon"><?= icon($it['icon']) ?></span>
          <h3 class="service__title"><?= e($it['title']) ?></h3>
          <?php if ($it['description']): ?><p class="service__desc"><?= nl2br(e($it['description'])) ?></p><?php endif; ?>
          <div class="service__foot">
            <?php if ($it['highlight']): ?><span class="service__price"><?= e($it['highlight']) ?></span><?php endif; ?>
            <?php if ($it['link_url']): ?><a class="service__link" href="<?= e(safe_link($it['link_url'])) ?>" aria-label="Ver más"><i class="fa-solid fa-arrow-right"></i></a><?php endif; ?>
          </div>
          <span class="service__shine" aria-hidden="true"></span>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
