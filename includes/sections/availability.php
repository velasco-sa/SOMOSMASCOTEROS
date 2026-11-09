<?php /** Simulador de disponibilidad */
$petTypes = db_all('SELECT p.id, p.name, p.icon, COALESCE(SUM(a.capacity),0) AS cap FROM pet_types p
                    LEFT JOIN accommodations a ON a.pet_type_id = p.id AND a.is_active = 1
                    WHERE p.is_active = 1 GROUP BY p.id, p.name, p.icon, p.sort_order ORDER BY p.sort_order, p.name');
$minAdvance = max(0, (int)setting('booking_min_advance_days', '0'));
$maxNights = max(1, (int)setting('booking_max_nights', '60'));
$requestsEnabled = setting('booking_requests_enabled', '1') === '1';
?>
<section class="section section--availability" id="<?= e($s['anchor']) ?>">
  <div class="availability__bg" aria-hidden="true"></div>
  <div class="container">
    <?php section_head($s); ?>
    <?php if ($s['body']): ?><div class="rich rich--center" data-reveal><?= sanitize_html($s['body']) ?></div><?php endif; ?>
    <div class="sim glass-strong" data-reveal
         data-min-advance="<?= $minAdvance ?>" data-max-nights="<?= $maxNights ?>"
         data-ok-msg="<?= e(setting('availability_ok_message')) ?>" data-full-msg="<?= e(setting('availability_full_message')) ?>">
      <form class="sim__form" id="simForm" novalidate>
        <div class="field">
          <label class="field__label" for="simPet"><i class="fa-solid fa-paw"></i> Tipo de mascota</label>
          <select id="simPet" name="pet_type_id" class="select2" data-placeholder="Selecciona" required>
            <option></option>
            <?php foreach ($petTypes as $pt): ?>
              <option value="<?= (int)$pt['id'] ?>" data-icon="<?= e($pt['icon']) ?>"><?= e($pt['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field field--grow">
          <label class="field__label" for="simDates"><i class="fa-regular fa-calendar"></i> Llegada y salida</label>
          <div class="input-icon">
            <i class="fa-regular fa-calendar-days"></i>
            <input type="text" id="simDates" class="input" placeholder="Elige las fechas" readonly required>
          </div>
        </div>
        <div class="field">
          <label class="field__label" for="simCount"><i class="fa-solid fa-hashtag"></i> Mascotas</label>
          <div class="stepper" data-stepper>
            <button type="button" data-step="-1" aria-label="Menos"><i class="fa-solid fa-minus"></i></button>
            <input type="number" id="simCount" name="pets_count" class="input" value="1" min="1" max="10">
            <button type="button" data-step="1" aria-label="Más"><i class="fa-solid fa-plus"></i></button>
          </div>
        </div>
        <button type="submit" class="btn btn--primary sim__submit"><i class="fa-solid fa-magnifying-glass"></i> <span>Consultar</span></button>
      </form>

      <div class="sim__result" id="simResult" hidden>
        <div class="sim__status" id="simStatus"></div>
        <div class="sim__days" id="simDays"></div>
        <div class="sim__legend">
          <span><i class="dot dot--ok"></i> Disponible</span>
          <span><i class="dot dot--low"></i> Pocos lugares</span>
          <span><i class="dot dot--full"></i> Sin lugar / cerrado</span>
        </div>
        <?php if ($requestsEnabled): ?>
          <div class="sim__cta" id="simCta" hidden>
            <button type="button" class="btn btn--accent" data-modal-open="bookingModal"><i class="fa-solid fa-paper-plane"></i> Solicitar esta reserva</button>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php if ($requestsEnabled): ?>
<div class="modal" id="bookingModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="bookingTitle">
  <div class="modal__backdrop" data-modal-close></div>
  <div class="modal__dialog">
    <button class="modal__close" data-modal-close aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    <div class="modal__head">
      <span class="modal__icon"><i class="fa-solid fa-calendar-check"></i></span>
      <div><h3 id="bookingTitle">Solicitar reserva</h3><p class="modal__sub" id="bookingSummary"></p></div>
    </div>
    <form id="bookingForm" class="form-grid" novalidate>
      <div class="field"><label class="field__label" for="bkName">Tu nombre</label><input class="input" id="bkName" name="full_name" required maxlength="120" autocomplete="name"></div>
      <div class="field"><label class="field__label" for="bkEmail">Correo</label><input class="input" id="bkEmail" type="email" name="email" required maxlength="160" autocomplete="email"></div>
      <div class="field"><label class="field__label" for="bkPhone">Teléfono / WhatsApp</label><input class="input" id="bkPhone" name="phone" required maxlength="30" autocomplete="tel"></div>
      <div class="field"><label class="field__label" for="bkPet">Nombre de tu mascota</label><input class="input" id="bkPet" name="pet_name" required maxlength="80"></div>
      <div class="field field--full"><label class="field__label" for="bkNotes">Notas (raza, edad, cuidados especiales)</label><textarea class="input" id="bkNotes" name="notes" rows="3" maxlength="1000"></textarea></div>
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
      <div class="modal__foot field--full">
        <button type="button" class="btn btn--ghost" data-modal-close>Cancelar</button>
        <button type="submit" class="btn btn--primary"><i class="fa-solid fa-paper-plane"></i> Enviar solicitud</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
