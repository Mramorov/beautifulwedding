/**
 * Marriage Form Handler
 */
(function() {
    'use strict';

    const form        = document.getElementById('marriageApplicationForm');
    const submitBtn   = form.querySelector('.submit-button');
    const successBlock = document.getElementById('anketaSuccessMessage');

    const urlParams = new URLSearchParams(window.location.search);
    const editHash  = urlParams.get('hash');

    if (editHash) {
        loadAnketaData(editHash);
        submitBtn.innerHTML = '<span class="btn-text">Сохранить изменения</span>';
    }

    // Показ/скрытие полей свидетельства о разводе по семейному положению
    function updateRozvodFields() {
        const groomStatus = form.querySelector('#groom_marital_status').value;
        const brideStatus = form.querySelector('#bride_marital_status').value;

        const rozvodGroomWrap = form.querySelector('.doc-rozvod-z-wrap');
        const rozvodBrideWrap = form.querySelector('.doc-rozvod-n-wrap');

        if (rozvodGroomWrap) {
            const hasFile = !!rozvodGroomWrap.querySelector('.doc-delete-btn');
            if (!hasFile) {
                const needs = groomStatus === 'Разведен' || groomStatus === 'Вдовец';
                rozvodGroomWrap.style.display = needs ? '' : 'none';
            }
        }
        if (rozvodBrideWrap) {
            const hasFile = !!rozvodBrideWrap.querySelector('.doc-delete-btn');
            if (!hasFile) {
                const needs = brideStatus === 'Разведена' || brideStatus === 'Вдова';
                rozvodBrideWrap.style.display = needs ? '' : 'none';
            }
        }
    }

    form.querySelector('#groom_marital_status').addEventListener('change', updateRozvodFields);
    form.querySelector('#bride_marital_status').addEventListener('change', updateRozvodFields);
    updateRozvodFields();

    // Кнопка «Удалить» для уже загруженных документов
    form.addEventListener('click', function(e) {
        if (!e.target.classList.contains('doc-delete-btn')) return;
        const btn   = e.target;
        const group = btn.closest('.doc-upload-group');

        group.querySelector('.doc-exists-state').style.display = 'none';
        group.querySelector('.doc-upload-state').style.display = 'block';
    });

    // Обновление предупреждения при выборе нового файла после удаления
    form.addEventListener('change', function(e) {
        const input = e.target;
        if (input.type !== 'file') return;
        const group = input.closest('.doc-upload-group');
        if (!group) return;
        const pendingText = group.querySelector('.doc-pending-text');
        if (!pendingText) return;

        const label = group.dataset.docLabel || '';
        if (input.files && input.files.length > 0) {
            pendingText.innerHTML = 'Файл <strong>' + label + '</strong> будет заменён новым после нажатия кнопки «Отправить»';
        } else {
            pendingText.innerHTML = 'Файл <strong>' + label + '</strong> будет удалён после нажатия кнопки «Отправить»';
        }
    });

    // Отправка формы
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const contactEmail = form.querySelector('#contact_email').value.trim();
        const contactTel   = form.querySelector('#contact_tel').value.trim();

        if (!contactEmail && !contactTel) {
            alert('Пожалуйста, укажите хотя бы один способ связи: email или телефон');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="btn-spinner">⏳ Отправка...</span>';

        const formData = new FormData(form);
        if (editHash) {
            formData.set('hash', editHash);
        }

        try {
            const response = await fetch(anketaParams.restUrl, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': anketaParams.nonce,
                },
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                handleSuccess(result);
            } else {
                handleError(result.message || 'Ошибка сохранения данных');
            }

        } catch (error) {
            console.error('Submission error:', error);
            handleError('Ошибка отправки. Проверьте подключение к интернету');
        }
    });

    function handleSuccess(result) {
        let editUrl = result.editUrl;
        if (!editUrl || editUrl.indexOf('http') !== 0) {
            editUrl = window.location.origin + window.location.pathname + '?hash=' + result.hash;
        }

        const editLinkAnchor = successBlock.querySelector('#editLinkAnchor');
        editLinkAnchor.href        = editUrl;
        editLinkAnchor.textContent = editUrl;

        successBlock.style.display = 'block';
        successBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });

        form.style.transition = 'opacity 0.3s ease-out';
        form.style.opacity    = '0';
        setTimeout(() => form.remove(), 300);
    }

    function handleError(message) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="btn-text">' + (editHash ? 'Сохранить изменения' : 'Отослать анкету') + '</span>';
        alert(message);
    }

    async function loadAnketaData(hash) {
        try {
            const response = await fetch(anketaParams.restUrl.replace('/submit', '/get') + '?hash=' + encodeURIComponent(hash));
            const result   = await response.json();

            if (result.success && result.data) {
                Object.keys(result.data).forEach(key => {
                    const field = form.querySelector('[name="' + key + '"]');
                    if (field && result.data[key]) {
                        field.value = result.data[key];
                    }
                });
                updateRozvodFields();
            } else {
                alert('Анкета не найдена. Будет создана новая анкета.');
                setTimeout(() => {
                    const baseUrl = window.location.origin + window.location.pathname;
                    if (window.location.href !== baseUrl) {
                        window.location.replace(baseUrl);
                    }
                }, 800);
            }
        } catch (error) {
            console.error('Load error:', error);
            alert('Ошибка загрузки данных анкеты');
        }
    }

    // Копирование ссылки
    const copyBtn = successBlock.querySelector('#copyLinkBtn');
    copyBtn.addEventListener('click', function() {
        const editLinkAnchor = successBlock.querySelector('#editLinkAnchor');
        const linkText = editLinkAnchor.textContent.trim();

        if (navigator.clipboard) {
            navigator.clipboard.writeText(linkText).then(() => {
                copyBtn.textContent = '✓ Скопировано!';
                setTimeout(() => { copyBtn.textContent = 'Скопировать'; }, 2000);
            }).catch(() => fallbackCopy(linkText));
        } else {
            fallbackCopy(linkText);
        }
    });

    function fallbackCopy(text) {
        const temp = document.createElement('textarea');
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        copyBtn.textContent = '✓ Скопировано!';
        setTimeout(() => { copyBtn.textContent = 'Скопировать'; }, 2000);
    }

})();
