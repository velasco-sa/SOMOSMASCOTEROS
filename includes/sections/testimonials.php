<?php /** Testimonios */ ?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container">
    <?php section_head($s); ?>
    <?php if ($s['body']): ?><div class="rich rich--center" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
    <div class="testimonials" data-slider>
      <div class="testimonials__track">
        <?php foreach ($s['items'] as $it): ?>
          <blockquote class="quote glass">
            <i class="fa-solid fa-quote-left quote__mark"></i>
            <?php if ($it['description']): ?><p><?= nl2br(e($it['description'])) ?></p><?php endif; ?>
            <footer class="quote__who">
              <span class="quote__avatar">
                <?php if ($it['image']): ?><img src="<?= e(url_for($it['image'])) ?>" alt="<?= e($it['title']) ?>" loading="lazy"><?php else: ?><?= icon($it['icon']) ?><?php endif; ?>
              </span>
              <span><strong><?= e($it['title']) ?></strong><?php if ($it['highlight']): ?><small><?= e($it['highlight']) ?></small><?php endif; ?></span>
              <span class="quote__stars" aria-label="5 estrellas"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></span>
            </footer>
          </blockquote>
        <?php endforeach; ?>
      </div>
      <?php if (count($s['items']) > 1): ?>
      <div class="slider-nav">
        <button type="button" data-slide="-1" aria-label="Anterior"><i class="fa-solid fa-arrow-left"></i></button>
        <div class="slider-dots"></div>
        <button type="button" data-slide="1" aria-label="Siguiente"><i class="fa-solid fa-arrow-right"></i></button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
