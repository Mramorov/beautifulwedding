(function ($) {
  'use strict';

  function initJustifiedGalleries() {
    if (typeof $.fn.justifiedGallery !== 'function') {
      return;
    }

    $('.svadba-gallery-grid').each(function () {
      var $gallery = $(this);

      if ($gallery.data('jgInited')) {
        return;
      }

      $gallery
        .justifiedGallery({
          rowHeight: 260,
          margins: 8,
          lastRow: 'left',
          justifyThreshold: 0.9,
          randomize: false,
          captions: false,
          cssAnimation: true,
          waitThumbnailsLoad: true,
          refreshTime: 250,
          maxRowHeight: 380,
          rel: $gallery.attr('id') || 'svadba-gallery'
        })
        .on('jg.complete', function () {
          $gallery.attr('data-jg-ready', '1');
        });

      $gallery.data('jgInited', true);
    });
  }

  $(document).ready(initJustifiedGalleries);
})(jQuery);
