(function($){
  
  // Update detail display for photo/video selects
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

  // Calculate sum of all selected services
  function calculateSum() {
    var sum = 0;
    
    // Sum all selected options from selects (except auto-hours-select)
    $('.select-element option:selected').each(function() {
      var $select = $(this).closest('select');
      
      // Skip auto-hours-select as it's used as multiplier
      if ($select.hasClass('auto-hours-select')) {
        return; // continue to next iteration
      }
      
      var value = parseFloat($(this).data('calculate')) || 0;
      
      // Special handling for auto-select: multiply by hours
      if ($select.hasClass('auto-select')) {
        var hours = parseFloat($('.auto-hours-select option:selected').data('calculate')) || 1;
        value *= hours;
      }
      
      sum += value;
    });
    
    // Sum all checked checkboxes
    $('.checkbox-element:checked').each(function() {
      var value = parseFloat($(this).data('calculate')) || 0;
      sum += value;
    });
    
    // Update services sum display
    $('#calcresult').text(Math.round(sum));
    
    // Update hidden field for form submission
    $('#calcField').val(sum);
    
    // Calculate total: base packet price + services
    var basePacketPrice = parseFloat($('#main-packet-sum').text()) || 0;
    var totalSum = basePacketPrice + sum;
    $('#total-calcresult').text(Math.round(totalSum));
  }

  // Handle auto-select change: show/hide hours selector
  function handleAutoSelect() {
    $('.auto-select').on('change', function() {
      if ($(this).prop('selectedIndex') === 0) {
        // "Выберите..." selected - hide hours
        $('.auto-hours-label, .auto-hours-wrap').slideUp(400);
      } else {
        // Car selected - show hours
        $('.auto-hours-label, .auto-hours-wrap').slideDown(400);
      }
      calculateSum();
    });
  }

  // Handle place selection
  function handlePlaceSelection() {
    $('.place-item').on('click', function() {
      var $this = $(this);
      var addPlacePrice = parseFloat($this.data('place-price')) || 0;
      var basePrice = parseFloat($('#main-packet-sum').data('mainpacket-sum')) || 0;
      
      // Remove active class from all places
      $('.place-item').removeClass('active-place');
      
      // Add active class to clicked place
      $this.addClass('active-place');
      
      // Calculate new main packet price
      var newMainPrice = basePrice + addPlacePrice;
      
      // Update display
      $('#main-packet-sum').text(newMainPrice);
      
      // Recalculate total sum
      calculateSum();
    });
  }

  // Handle slideshow checkbox dependency on photo selection
  function handleSlideshowDependency() {
    // Update checkbox state when photo select changes
    function updateSlideshowCheckbox() {
      var selectedValue = $('.photo-select').val();
      var checkbox = $('input[type="checkbox"][value="Слайд-шоу (только в дополнение к фотосъёмке свадебной церемонии)."]');
      
      if (checkbox.length === 0) return; // Exit if checkbox doesn't exist
      
      var parentLabel = checkbox.parent();
      var errorMessageDiv = parentLabel.next('.error-message');

      if (!selectedValue || selectedValue === '' || selectedValue === 'Выберите...') {
        // No photo hours selected
        checkbox.prop('checked', false);
        checkbox.prop('disabled', true);
        
        if (errorMessageDiv.length === 0) {
          parentLabel.after('<div class="error-message" style="color: #d32f2f; font-size: 0.85rem; margin-top: 4px;">Выберите какое-то количество часов фотосъёмки</div>');
        }
      } else {
        // Photo hours selected
        checkbox.prop('disabled', false);
        errorMessageDiv.remove();
      }
    }

    // Attach event listener to photo select
    $('.photo-select').on('change', updateSlideshowCheckbox);

    // Prevent clicking on disabled slideshow checkbox
    $(document).on('click', 'input[type="checkbox"][value="Слайд-шоу (только в дополнение к фотосъёмке свадебной церемонии)."]', function(e) {
      var selectedValue = $('.photo-select').val();
      if (!selectedValue || selectedValue === '' || selectedValue === 'Выберите...') {
        e.preventDefault();
        var parentLabel = $(this).parent();
        var errorMessageDiv = parentLabel.next('.error-message');
        
        if (errorMessageDiv.length === 0) {
          parentLabel.after('<div class="error-message" style="color: #d32f2f; font-size: 0.85rem; margin-top: 4px;">Выберите какое-то количество часов фотосъёмки</div>');
        }
      }
    });

    // Initialize state on page load
    updateSlideshowCheckbox();
  }

  function init() {
    // Detail display for photo and video selects
    $(document).on('change', '.photo-select, .video-select', function(){
      updateDetailForSelect($(this));
    });

    // Initialize existing selects on page load
    $('.photo-select, .video-select').each(function(){
      updateDetailForSelect($(this));
    });
    
    // Attach calculator to all selects and checkboxes
    $('.select-element, .checkbox-element').on('change', calculateSum);
    
    // Handle auto-select special behavior
    handleAutoSelect();
    
    // Handle place selection
    handlePlaceSelection();
    
    // Handle slideshow checkbox dependency
    handleSlideshowDependency();
    
    // Initial calculation on page load
    calculateSum();
  }

  // Wait for document ready
  if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($){ init(); });
  }
})(jQuery);
