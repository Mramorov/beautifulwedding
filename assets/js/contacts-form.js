(function () {
  'use strict';

  var form = document.getElementById('contactsAjaxForm');
  if (!form || typeof bwContactsForm === 'undefined') {
    return;
  }

  var status = document.getElementById('contactsFormStatus');
  var submitButton = form.querySelector('button[type="submit"]');

  var setStatus = function (message, isError) {
    if (!status) {
      return;
    }

    status.textContent = message;
    status.classList.toggle('is-error', !!isError);
    status.classList.toggle('is-success', !isError && message.length > 0);
  };

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    var formData = new FormData(form);
    formData.append('action', 'bw_submit_contacts_form');
    formData.append('nonce', bwContactsForm.nonce);

    setStatus('Отправка...', false);
    if (submitButton) {
      submitButton.disabled = true;
    }

    fetch(bwContactsForm.ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('network_error');
        }
        return response.json();
      })
      .then(function (data) {
        if (!data || !data.success) {
          throw new Error((data && data.data && data.data.message) || 'send_error');
        }

        form.reset();
        setStatus('Спасибо! Ваше сообщение успешно отправлено.', false);
      })
      .catch(function () {
        setStatus('Не удалось отправить форму. Попробуйте еще раз.', true);
      })
      .finally(function () {
        if (submitButton) {
          submitButton.disabled = false;
        }
      });
  });
})();
