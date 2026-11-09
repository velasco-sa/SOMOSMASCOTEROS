<!-- Selector de imágenes (biblioteca de medios) -->
<div class="modal modal--xl" id="mediaModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="mediaModalTitle">
  <div class="modal__backdrop" data-modal-close></div>
  <div class="modal__dialog">
    <button class="modal__close" data-modal-close aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
    <div class="modal__head">
      <span class="modal__icon"><i class="fa-solid fa-photo-film"></i></span>
      <div><h3 id="mediaModalTitle">Biblioteca de imágenes</h3><p class="modal__sub">Elige una imagen o sube una nueva.</p></div>
    </div>
    <div class="media-picker" data-media-picker>
      <div class="media-picker__bar">
        <div class="toolbar__search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" class="input" data-media-search placeholder="Buscar imagen…"></div>
        <label class="btn btn--secondary" data-media-upload-btn><i class="fa-solid fa-cloud-arrow-up"></i> <span>Subir imágenes</span><input type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden data-media-file></label>
      </div>
      <div class="dropzone" data-media-drop><i class="fa-solid fa-cloud-arrow-up"></i><strong>Arrastra aquí tus imágenes</strong><span>JPG, PNG, WEBP o GIF · se optimizan automáticamente</span></div>
      <div class="media-grid" data-media-grid></div>
      <footer class="pager" data-media-pager></footer>
    </div>
  </div>
</div>
