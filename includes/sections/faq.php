<?php /** Preguntas frecuentes */ ?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container container--narrow">
    <?php section_head($s); ?>
    <?php if ($s['body']): ?><div class="rich rich--center" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
    <div class="faq">
      <?php foreach ($s['items'] as $k => $it): ?>
        <details class="faq__item" data-reveal <?= $k === 0 ? 'open' : '' ?>>
          <summary><span class="faq__icon"><?= icon($it['icon'], 'fa-solid fa-circle-question') ?></span><span class="faq__q"><?= e($it['title']) ?></span><i class="fa-solid fa-chevron-down faq__chev"></i></summary>
          <div class="faq__a"><p><?= nl2br(e($it['description'])) ?></p></div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
