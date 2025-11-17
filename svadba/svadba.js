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
    
    if ($select.hasClass('photo-select')) {
      $detailContainer = $('.select-detail[data-for="photo"]');
    } else if ($select.hasClass('video-select')) {
      $detailContainer = $('.select-detail[data-for="video"]');
    } else {
      return;
    }

    var detail = $select.find('option:selected').data('detail');
    if (detail) {
      $detailContainer.html(detail).show();
    } else {
      $detailContainer.empty().hide();
    }
  }

  function calculateSum() {
    var servicesSum = 0;

    var $autoSelect = $('.auto-select');
    var distance = parseInt($autoSelect.data('distance')) || 1;
    var baseAutoPrice = parseFloat($autoSelect.data('base-auto-price')) || 0;
    var selectedAutoPrice = parseFloat($autoSelect.find('option:selected').data('calculate')) || 0;
    var $hoursSelect = $('.auto-hours-select');
    var selectedHours = $hoursSelect.length ? (parseInt($hoursSelect.val()) || distance) : distance;
    var isBaseAuto = $autoSelect.prop('selectedIndex') === 0;

    var baseAutoDeduction = Math.round((baseAutoPrice * distance * 0.7) / 10) * 10;

    $('.select-element option:selected').each(function() {
      var $select = $(this).closest('select');
      if ($select.hasClass('auto-select')) return;
      var value = parseFloat($(this).data('calculate')) || 0;
      servicesSum += value;
    });

    var autoCost = 0;
    if (!(isBaseAuto && selectedHours === distance)) {
      autoCost = -baseAutoDeduction + (selectedAutoPrice * selectedHours);
    }
    servicesSum += autoCost;

    $('.checkbox-element:checked').each(function() {
      var value = parseFloat($(this).data('calculate')) || 0;
      servicesSum += value;
    });

    var placeAddition = parseFloat($('.place-item.active-place').data('place-price')) || 0;
    var originalBase = parseFloat($('#main-packet-sum').data('mainpacket-sum')) || 0;

    $('#calcresult').text(Math.round(servicesSum));
    $('#calcField').val(servicesSum);

    var totalSum = originalBase + placeAddition + servicesSum;
    $('#total-calcresult').text(Math.round(totalSum));
  }

  function handlePlaceSelection() {
    $('.place-item').on('click', function() {
      var $this = $(this);
      var addPlacePrice = parseFloat($this.data('place-price')) || 0;
      var basePrice = parseFloat($('#main-packet-sum').data('mainpacket-sum')) || 0;
      
      $('.place-item').removeClass('active-place');
      $this.addClass('active-place');
      var newMainPrice = basePrice + addPlacePrice;
      $('#main-packet-sum').text(newMainPrice);
      
      $('.packets-grid .packet-price .price-value').each(function() {
        var $priceSpan = $(this);
        var basePacketPrice = parseFloat($priceSpan.data('init-price')) || 0;
        var newPacketPrice = basePacketPrice + addPlacePrice;
        $priceSpan.text(newPacketPrice);
      });
      
      calculateSum();
    });
  }

  function openOrderModal(formId, captionText, priceText) {
    $('#contactForm').data('formid', formId);
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

  $(document).on('click', '#orderButton', function(){
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

  function validateEmail(email) {
    var regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return regex.test(email);
  }

  function prepareIndividData(fd) {
    fd.append('Пакет', 'Индивидуальный');
    fd.append('Цена', $('#total-calcresult').text());
    
    var individForm = new FormData($('#individForm')[0]);
    individForm.forEach(function(value, key){
      if (!value || value === '' || value === 'Выберите...') return;
      var russianLabel = fieldLabels[key] || key;
      fd.append(russianLabel, value);
    });
  }

  $(document).on('click', '#sendButton', function(){
    var formName = $('#contactForm').data('formid');
    var messageDiv = $('#individ-message-form');
    var fd = new FormData($('#contactForm')[0]);

    var name = ($('#name').val()||'').trim();
    var email = ($('#email').val()||'').trim();
    var errors = [];
    if (!name) errors.push('Поле "Имя" не может быть пустым.');
    if (!email) errors.push('Поле "Email" не может быть пустым.');
    else if (!validateEmail(email)) errors.push('Некорректный формат email.');
    if (errors.length) { $('#fill-error-mess').html(errors.join('<br/>')).addClass('send-error'); return; }

    var placeName = $('.place-item.active-place').data('place-name') || '';
    fd.append('Локация', placeName);

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

    $('.photo-select').on('change', updateSlideshowCheckbox);
    $(document).on('click', SLIDESHOW_SELECTOR, function(e) {
      if (!isPhotoSelected()) {
        e.preventDefault();
        showError($(this).parent().find('.chk-text-wrapper'));
      }
    });
    updateSlideshowCheckbox();
  }

  function handleTabs() {
    function activateTab(targetTab) {
      if (!targetTab) return;
      $('.svadba-tab-button').removeClass('active');
      $('.svadba-tab-pane').removeClass('active');
      $('.svadba-tab-button[data-tab="' + targetTab + '"]').addClass('active');
      $('#' + targetTab).addClass('active');
    }

    function scrollToTabSection() {
      var $section = $('.svadba-tabs-section');
      if ($section.length) {
        var offset = $section.offset().top;
        $('html, body').animate({ scrollTop: offset }, 500);
      }
    }

    $(document).on('click', '.svadba-tab-button', function(){
      activateTab($(this).data('tab'));
    });

    $(document).on('click', '.svadba-tab-link', function(e){
      e.preventDefault();
      var $link = $(this);
      var targetTab = $link.data('tab-target') || ($link.attr('href') ? $link.attr('href').replace('#', '') : '');
      activateTab(targetTab);
      scrollToTabSection();
    });
  }

  function init() {
    $(document).on('change', '.photo-select, .video-select', function(){
      updateDetailForSelect($(this));
    });
    $('.photo-select, .video-select').each(function(){
      updateDetailForSelect($(this));
    });
    
    $('.select-element, .checkbox-element').on('change', calculateSum);
    handlePlaceSelection();
    handleSlideshowDependency();
    handleTabs();
    $(document).on('change', '.auto-hours-select', calculateSum);
    calculateSum();
  }

  if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($){ init(); });
  }
})(jQuery);
