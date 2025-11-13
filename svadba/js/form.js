(function($){
  
  // Field name translations for email (name attribute -> Russian label)
  var fieldLabels = {
    'trans': 'Автомобиль для трансфера из аэропорта в отель и обратно',
    'auto': 'Автомобиль в день бракосочетания',
    'photo': 'Фотосъёмка свадебной церемонии, прогулки по Праге (часов)',
    'video': 'Видеосъёмка свадебной церемонии, прогулки по Праге (часов)',
    'bqt': 'Букет невесты',
    'cake': 'Свадебный торт',
    'post': 'Отправка экспресс почтой EMS/DHL/Fedex свидетельства о браке',
    'car_hours': 'Время автомобиля (час)',
    'additional_services[]': 'Другие услуги[]',
    'services_sum': 'В том числе дополнительных услуг на сумму'
  };
  
  // Update detail display for photo/video selects
  function updateDetailForSelect($select) {
    var $detailContainer;
    
    // Determine detail container by select class
    if ($select.hasClass('photo-select')) {
      $detailContainer = $('.select-detail[data-for="photo"]');
    } else if ($select.hasClass('video-select')) {
      $detailContainer = $('.select-detail[data-for="video"]');
    } else {
      return; // No detail container for other selects
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
    
    // Get base auto calculation data
    var $autoSelect = $('.auto-select');
    var distance = parseInt($autoSelect.data('distance')) || 1;
    var baseAutoPrice = parseFloat($autoSelect.data('base-auto-price')) || 0;
    var selectedAutoPrice = parseFloat($autoSelect.find('option:selected').data('calculate')) || 0;
    var selectedHours = distance;
    var isBaseAuto = $autoSelect.prop('selectedIndex') === 0;
    
    // Calculate base auto deduction (70% of base auto price × distance, rounded to tens)
    var baseAutoDeduction = Math.round((baseAutoPrice * distance * 0.7) / 10) * 10;
    
    // Sum all selected options from selects (except auto and auto-hours)
    $('.select-element option:selected').each(function() {
      var $select = $(this).closest('select');
      
      // Skip auto-select (handled separately)
      if ($select.hasClass('auto-select')) {
        return;
      }
      
      var value = parseFloat($(this).data('calculate')) || 0;
      sum += value;
    });
    
    // Calculate auto cost with new logic
    var autoCost = 0;
    if (isBaseAuto && selectedHours === distance) {
      // Base auto + minimum hours = already included in base package
      autoCost = 0;
    } else {
      // Any other combination: subtract base, add new
      autoCost = -baseAutoDeduction + (selectedAutoPrice * selectedHours);
    }
    sum += autoCost;
    
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
      
      // Update fixed packets prices (from packets grid)
      $('.packets-grid .packet-price .price-value').each(function() {
        var $priceSpan = $(this);
        var basePacketPrice = parseFloat($priceSpan.data('init-price')) || 0;
        var newPacketPrice = basePacketPrice + addPlacePrice;
        $priceSpan.text(newPacketPrice);
      });
      
      // Recalculate total sum
      calculateSum();
    });
  }

  // Modal helpers
  function openOrderModal(formId, captionText, priceText) {
    // store form id on form
    $('#contactForm').data('formid', formId);
    // remove old caption
    $('.send-caption-price-wrap').remove();
    if (captionText && priceText) {
      var wrap = $('<div>', { 'class': 'send-caption-price-wrap' });
      $('<div>', { 'class': 'send-caption', text: 'Пакет: ' + captionText }).appendTo(wrap);
      var priceDiv = $('<div>', { 'class': 'send-price' });
      priceDiv.text('Цена: ').append($('<span>', { text: priceText })).append(' €');
      wrap.insertBefore('#closeButton');
    }
    $('#sendButton').show();
    $('#sending-process').hide();
    $('#fill-error-mess').removeClass().text('');
    $('#modalOverlay, #modal').addClass('active');
  }

  function closeOrderModal() {
    $('#modalOverlay, #modal').removeClass('active');
  }

  // Click handlers to open modal
  $(document).on('click', '#orderButton', function(){
    // Individual
    openOrderModal('individ', 'индивидуальный', $('#total-calcresult').text());
  });
  $(document).on('click', '#main_order_button', function(){
    openOrderModal('main', 'базовый', $('#main-packet-sum').text());
  });
  $(document).on('click', '.packet-order', function(){
    var formId = $(this).data('formid');
    var name = $('#name-' + formId).text();
    var price = $('#price-' + formId).text();
    openOrderModal(formId, name, price);
  });

  $(document).on('click', '#closeButton, #cancelButton', function(){ closeOrderModal(); });

  // Email validator
  function validateEmail(email) {
    var regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return regex.test(email);
  }

  function prepareIndividData(fd) {
    fd.append('Пакет', 'Индивидуальный');
    fd.append('Цена', $('#total-calcresult').text());
    
    var individForm = new FormData($('#individForm')[0]);
    individForm.forEach(function(value, key){
      // Skip empty values and "Выберите..." placeholder
      if (!value || value === '' || value === 'Выберите...') return;
      
      // Direct mapping: if key exists in fieldLabels, use it; otherwise keep original
      var russianLabel = fieldLabels[key] || key;
      
      fd.append(russianLabel, value);
    });
  }

  // Send
  $(document).on('click', '#sendButton', function(){
    var formName = $('#contactForm').data('formid');
    var messageDiv = $('#individ-message-form');
    var fd = new FormData($('#contactForm')[0]);

    // Required fields
    var name = ($('#name').val()||'').trim();
    var email = ($('#email').val()||'').trim();
    var errors = [];
    if (!name) errors.push('Поле "Имя" не может быть пустым.');
    if (!email) errors.push('Поле "Email" не может быть пустым.');
    else if (!validateEmail(email)) errors.push('Некорректный формат email.');
    if (errors.length) { $('#fill-error-mess').html(errors.join('<br/>')).addClass('send-error'); return; }

    // Common info
    var placeName = $('.place-item.active-place').data('place-name') || '';
    fd.append('Локация', placeName);

    // Form-specific
    if (formName === 'individ') {
      messageDiv = $('#individ-message-form');
      prepareIndividData(fd);
    } else if (formName === 'main') {
      messageDiv = $('#main-message-form');
      fd.append('Пакет', 'Базовый');
      fd.append('Цена', $('#main-packet-sum').text());
    } else if (String(formName).indexOf('packet-') === 0) {
      messageDiv = $('#packet-message-form');
      fd.append('Пакет', $('#name-' + formName).text());
      fd.append('Цена', $('#price-' + formName).text());
    }

    // UI state
    $('#sendButton').hide();
    $('#sending-process').show();

    $.ajax({
      url: (window.customFormParams && customFormParams.restUrl) || '',
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      beforeSend: function(xhr){ if (window.customFormParams) xhr.setRequestHeader('X-WP-Nonce', customFormParams.nonce); },
      success: function(data){
        if (data && data.success) messageDiv.text('Заказ успешно отправлен!').addClass('send-success');
        else messageDiv.text('Произошла ошибка при отправке заказа.').addClass('send-error');
        closeOrderModal();
      },
      error: function(){
        messageDiv.text('Произошла ошибка при отправке заказа.').addClass('send-error');
        closeOrderModal();
      }
    });
  });

  // Handle slideshow checkbox dependency on photo selection
  function handleSlideshowDependency() {
    var SLIDESHOW_SELECTOR = 'input[type="checkbox"][value="Слайд-шоу (только в дополнение к фотосъёмке свадебной церемонии)."]';
    var ERROR_MESSAGE = 'Для этой опции надо выбрать часы фотосъёмки';
    
    function isPhotoSelected() {
      var value = $('.photo-select').val();
      return value && value !== '' && value !== 'Выберите...';
    }
    
    function showError($textWrapper) {
      if ($textWrapper.find('.form-error-message').length === 0) {
        $textWrapper.append('<div class="form-error-message">' + ERROR_MESSAGE + '</div>');
      }
    }
    
    function hideError($textWrapper) {
      $textWrapper.find('.form-error-message').remove();
    }
    
    function updateSlideshowCheckbox() {
      var $checkbox = $(SLIDESHOW_SELECTOR);
      if ($checkbox.length === 0) return;
      
      var $textWrapper = $checkbox.parent().find('.chk-text-wrapper');
      
      if (isPhotoSelected()) {
        $checkbox.prop('disabled', false);
        hideError($textWrapper);
      } else {
        $checkbox.prop('checked', false).prop('disabled', true);
        showError($textWrapper);
      }
    }

    // Attach event listener to photo select
    $('.photo-select').on('change', updateSlideshowCheckbox);

    // Prevent clicking on disabled slideshow checkbox
    $(document).on('click', SLIDESHOW_SELECTOR, function(e) {
      if (!isPhotoSelected()) {
        e.preventDefault();
        showError($(this).parent().find('.chk-text-wrapper'));
      }
    });

    // Initialize state on page load
    updateSlideshowCheckbox();
  }

  // Handle tabs switching
  function handleTabs() {
    $('.svadba-tab-button').on('click', function(){
      var $button = $(this);
      var targetTab = $button.data('tab');
      
      // Remove active from all buttons and panes
      $('.svadba-tab-button').removeClass('active');
      $('.svadba-tab-pane').removeClass('active');
      
      // Add active to clicked button and corresponding pane
      $button.addClass('active');
      $('#' + targetTab).addClass('active');
    });
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
    
    // Handle place selection
    handlePlaceSelection();
    
    // Handle slideshow checkbox dependency
    handleSlideshowDependency();
    
    // Handle tabs
    handleTabs();
    
    // Initial calculation on page load
    calculateSum();
  }

  // Wait for document ready
  if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($){ init(); });
  }
})(jQuery);
