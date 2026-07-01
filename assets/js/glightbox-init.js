document.addEventListener('DOMContentLoaded', () => {
  if (typeof GLightbox === 'undefined') return;

  const lightbox = GLightbox({
    selector: '.svadba-gallery-item.glightbox',
    touchNavigation: true,
    loop: true,
    closeOnOutsideClick: true,
  });
});
