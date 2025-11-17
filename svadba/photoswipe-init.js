document.addEventListener('DOMContentLoaded', () => {
  if (typeof PhotoSwipeLightbox === 'undefined' || typeof PhotoSwipe === 'undefined') {
    return;
  }

  document.querySelectorAll('.svadba-gallery-grid[id]').forEach((gallery) => {
    const lightbox = new PhotoSwipeLightbox({
      gallery: `#${gallery.id}`,
      children: 'a',
      wheelToZoom: true,
      pswpModule: PhotoSwipe,
    });

    lightbox.init();
  });
});