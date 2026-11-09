<?php
declare(strict_types=1);
require __DIR__ . '/includes/init.php';
require_login();
require_perm('media');
admin_header('Biblioteca de medios', 'media', ['subtitle' => 'Todas las imágenes del sitio en un solo lugar']);
?>
<div class="alert alert--info crud__intro"><i class="fa-solid fa-lightbulb"></i><span>Sube aquí tus fotos y después elígelas desde cualquier sección, galería o configuración. Haz clic en una imagen para editar su descripción (ayuda al SEO y a la accesibilidad).</span></div>
<div class="card media-page" data-media-picker data-mode="manage">
  <div class="media-picker__bar">
    <div class="toolbar__search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" class="input" data-media-search placeholder="Buscar imagen…"></div>
    <?php if (can('media', 'create')): ?>
      <label class="btn btn--primary" data-media-upload-btn><i class="fa-solid fa-cloud-arrow-up"></i> <span>Subir imágenes</span><input type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden data-media-file></label>
    <?php endif; ?>
  </div>
  <?php if (can('media', 'create')): ?>
    <div class="dropzone" data-media-drop><i class="fa-solid fa-cloud-arrow-up"></i><strong>Arrastra aquí tus imágenes</strong><span>JPG, PNG, WEBP o GIF · máximo <?= (int)$CONFIG['max_upload_mb'] ?> MB cada una</span></div>
  <?php endif; ?>
  <div class="media-grid" data-media-grid></div>
  <footer class="pager" data-media-pager></footer>
</div>

<div class="modal" id="mediaEdit" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="mediaEditTitle">
  <div class="modal__backdrop" data-modal-close></div>
  <div class="modal__dialog">
    <button class="modal__close" data-modal-close aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    <div class="modal__head"><span class="modal__icon"><i class="fa-solid fa-image"></i></span><div><h3 id="mediaEditTitle">Detalle de imagen</h3><p class="modal__sub" data-me-name></p></div></div>
    <div class="media-edit">
      <div class="media-edit__preview"><img src="" alt="" data-me-img></div>
      <div class="media-edit__info">
        <div class="field"><label class="field__label" for="meAlt">Descripción (texto alternativo)</label><input class="input" id="meAlt" maxlength="255" <?= can('media', 'edit') ? '' : 'disabled' ?>></div>
        <dl class="meta-list" data-me-meta></dl>
        <div class="field"><label class="field__label">Dirección del archivo</label><div class="input-icon"><i class="fa-solid fa-link"></i><input class="input" readonly data-me-url><button type="button" class="input-icon__action" data-copy title="Copiar"><i class="fa-regular fa-copy"></i></button></div></div>
      </div>
    </div>
    <div class="modal__foot">
      <?php if (can('media', 'delete')): ?><button type="button" class="btn btn--danger" data-me-delete><i class="fa-solid fa-trash-can"></i> Eliminar</button><?php endif; ?>
      <span class="spacer"></span>
      <button type="button" class="btn btn--ghost" data-modal-close>Cerrar</button>
      <?php if (can('media', 'edit')): ?><button type="button" class="btn btn--primary" data-me-save><i class="fa-solid fa-floppy-disk"></i> Guardar</button><?php endif; ?>
    </div>
  </div>
</div>
<?php admin_footer(['page' => 'media']); ?>
