<?php /** Cuidados */ ?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container">
    <?php section_head($s); ?>
    <?php if ($s['body']): ?><div class="rich rich--center" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
    <div class="care-grid">
      <?php foreach ($s['items'] as $k => $it): ?>
        <article class="care" data-reveal style="--d:<?= ($k % 3) * 80 ?>ms">
          <span class="care__icon"><?= icon($it['icon']) ?></span>
          <div>
            <h3><?= e($it['title']) ?></h3>
            <?php if ($it['description']): ?><p><?= nl2br(e($it['description'])) ?></p><?php endif; ?>
            <?php if ($it['highlight']): ?><span class="chip"><?= e($it['highlight']) ?></span><?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php if ($s['image']): ?><div class="wide-image" data-reveal><img src="<?= e(url_for($s['image'])) ?>" alt="<?= e($s['image_alt'] ?: $s['title']) ?>" loading="lazy"></div><?php endif; ?>
  </div>
</section>
