/**
 * Marriage Form Handler
 */
(function() {
    'use strict';

    const form = document.getElementById('marriageApplicationForm');
    const submitBtn = form.querySelector('.submit-button');
    const successBlock = document.getElementById('anketaSuccessMessage');
    
    // Проверка наличия hash в URL для режима редактирования
    const urlParams = new URLSearchParams(window.location.search);
    const editHash = urlParams.get('hash');
    
    /**
     * Загрузить данные анкеты по hash при открытии страницы
     */
    if (editHash) {
        loadAnketaData(editHash);
        submitBtn.innerHTML = '<span class="btn-text">Сохранить изменения</span>';
    }
    
    /**
     * Обработчик отправки формы
     */
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Валидация контактных полей: должно быть заполнено хотя бы одно
        const contactEmail = form.querySelector('#contact_email').value.trim();
        const contactTel = form.querySelector('#contact_tel').value.trim();
        
        if (!contactEmail && !contactTel) {
            alert('Пожалуйста, укажите хотя бы один способ связи: email или телефон');
            return;
        }
        
        // Заблокировать кнопку и показать индикатор загрузки
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="btn-spinner">⏳ Отправка...</span>';
        
        // Собрать данные формы
        const formData = new FormData(form);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        
        // Добавить hash если это редактирование
        if (editHash) {
            data.hash = editHash;
        }
        
        try {
            const response = await fetch(anketaParams.restUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
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
    
    /**
     * Обработка успешной отправки
     */
    function handleSuccess(result) {
        // Сформировать абсолютную ссылку (игнорируем возможный относительный editUrl)
        let editUrl = result.editUrl;
        if (!editUrl || editUrl.indexOf('http') !== 0) {
            // Строим от текущей страницы
            editUrl = window.location.origin + window.location.pathname + '?hash=' + result.hash;
        }

        const editLinkAnchor = successBlock.querySelector('#editLinkAnchor');
        editLinkAnchor.href = editUrl;
        editLinkAnchor.textContent = editUrl;

        // Показать success-блок
        successBlock.style.display = 'block';
        successBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });

        // Анимация исчезновения формы
        form.style.transition = 'opacity 0.3s ease-out';
        form.style.opacity = '0';

        // Удалить форму из DOM через 300ms
        setTimeout(() => {
            form.remove();
        }, 300);
    }
    
    /**
     * Обработка ошибки
     */
    function handleError(message) {
        // Разблокировать кнопку
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="btn-text">' + (editHash ? 'Сохранить изменения' : 'Отправить заявление') + '</span>';
        
        // Показать сообщение об ошибке
        alert(message);
        
        // Можно добавить красивый alert-блок вместо alert()
    }
    
    /**
     * Загрузить данные анкеты по hash
     */
    async function loadAnketaData(hash) {
        try {
            const response = await fetch(anketaParams.restUrl.replace('/submit', '/get') + '?hash=' + encodeURIComponent(hash));
            const result = await response.json();
            
            if (result.success && result.data) {
                // Заполнить все поля формы
                Object.keys(result.data).forEach(key => {
                    const field = form.querySelector('[name="' + key + '"]');
                    if (field && result.data[key]) {
                        field.value = result.data[key];
                    }
                });
            } else {
                alert('Анкета не найдена. Будет создана новая анкета.');
                // Перенаправить без параметра hash, чтобы гарантированно создать новую запись
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
    
    /**
     * Копирование ссылки в буфер обмена
     */
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
