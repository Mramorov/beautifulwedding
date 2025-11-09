(function($){
  // Simple behavior: when photo-select or video-select changes, ее
  // find the selected option's data-detail-id and show corresponding HTML
  
  function updateDetailForSelect($select) {
    var selKey = $select.attr('id') || $select.attr('name') || '';
    var $detailContainer = $('.select-detail[data-for="' + selKey + '"]');
    if (!$detailContainer.length) {
      // fallback: try by class name (photo/video)
      if ($select.hasClass('photo-select')) $detailContainer = $('.select-detail[data-for="photo"]');
      if ($select.hasClass('video-select')) $detailContainer = $('.select-detail[data-for="video"]');
    }

    var detail = $select.find('option:selected').data('detail');
    if (detail) {
      $detailContainer.html(detail).show();
    } else {
      $detailContainer.empty().hide();
    }
  }

  function init() {
    // on change for photo and video selects (and any matching *-select if needed)
    $(document).on('change', '.photo-select, .video-select', function(){
      updateDetailForSelect($(this));
    });

    // initialize existing selects on page load
    $('.photo-select, .video-select').each(function(){
      updateDetailForSelect($(this));
    });
  }

  // Wait for document ready
  if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($){ init(); });
  }
})(jQuery);
