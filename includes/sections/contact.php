<?php /** Contacto */ global $contactChannels, $socialLinks; ?>
<section class="section <?= $alt ? 'section--alt' : '' ?>" id="<?= e($s['anchor']) ?>">
  <div class="container contact">
    <div class="contact__info">
      <?php section_head($s, 'left'); ?>
      <?php if ($s['body']): ?><div class="rich" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
      <ul class="channels" data-reveal>
        <?php foreach ($contactChannels as $c): $href = channel_href($c); ?>
          <li><a class="channel" href="<?= e($href) ?>" <?= str_starts_with($href, 'http') ? 'target="_blank" rel="noopener"' : '' ?>>
            <span class="channel__icon"><?= icon($c['icon']) ?></span>
            <span><small><?= e($c['label']) ?></small><strong><?= e($c['value']) ?></strong></span>
            <i class="fa-solid fa-arrow-up-right-from-square channel__go"></i>
          </a></li>
        <?php endforeach; ?>
      </ul>
      <?php if ($socialLinks): ?>
        <div class="socials socials--lg" data-reveal>
          <?php foreach ($socialLinks as $sl): ?>
            <a href="<?= e(safe_link($sl['url'])) ?>" target="_blank" rel="noopener" aria-label="<?= e($sl['name']) ?>" title="<?= e($sl['name']) ?>"><?= icon($sl['icon']) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <form class="contact__form glass-strong" id="contactForm" data-reveal="right" novalidate>
      <h3 class="contact__form-title"><i class="fa-solid fa-paper-plane"></i> Envíanos un mensaje</h3>
      <div class="form-grid">
        <div class="field"><label class="field__label" for="ctName">Nombre</label><input class="input" id="ctName" name="full_name" required maxlength="120" autocomplete="name"></div>
        <div class="field"><label class="field__label" for="ctEmail">Correo</label><input class="input" id="ctEmail" name="email" type="email" required maxlength="160" autocomplete="email"></div>
        <div class="field"><label class="field__label" for="ctPhone">Teléfono</label><input class="input" id="ctPhone" name="phone" maxlength="30" autocomplete="tel"></div>
        <div class="field"><label class="field__label" for="ctSubject">Asunto</label><input class="input" id="ctSubject" name="subject" maxlength="160"></div>
        <div class="field field--full"><label class="field__label" for="ctMsg">Mensaje</label><textarea class="input" id="ctMsg" name="message" rows="5" required maxlength="3000"></textarea></div>
        <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
      </div>
      <button type="submit" class="btn btn--primary btn--block"><i class="fa-solid fa-paper-plane"></i> Enviar mensaje</button>
    </form>
  </div>
</section>
