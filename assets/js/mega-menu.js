/**
 * Логика работы мега-меню
 * 
 * Отвечает за интерактивность выпадающих блоков:
 * - При наведении на элемент списка мест показывает информацию в баннере
 * - Меняет фоновое изображение баннера
 * - Восстанавливает исходное состояние при уходе курсора
 * 
 * @package BeautifulWedding
 */

jQuery(function($) {
    'use strict';

    // Лейблы для отображения данных в баннере
    const labels = {
        fromnew: 'Цена',
        capacity: 'Вместимость',
        mesta: 'Места',
        ceremonies: 'Церемония',
        wedding_days: 'Дни проведения'
    };

    /**
     * Обработчик наведения на элемент списка мест
     */
    $(document).on('mouseenter', '.mega-menu-list li', function() {
        const $item = $(this);
        const dataInfo = $item.attr('data-info');
        const dataBg = $item.attr('data-bg');

        // Пропускаем пустые элементы
        if ($item.hasClass('empty-list')) {
            return;
        }

        // Находим баннер и контейнер для текста
        const $banner = $item.closest('.mega-menu-inner').find('.right-mega-banner');
        const $textblock = $banner.find('.banner-textblock');

        // Сохраняем оригинальное содержимое при первом наведении
        if (!$textblock.data('original')) {
            $textblock.data('original', $textblock.html());
        }

        // Парсим данные места
        let data = {};
        try {
            data = dataInfo ? JSON.parse(dataInfo) : {};
        } catch(e) {
            console.warn('Ошибка парсинга data-info:', e);
            return;
        }

        // Очищаем контейнер
        $textblock.empty();

        // Рендерим информацию о месте
        $.each(data, function(key, value) {
            // Пропускаем пустые значения
            if (!value || (Array.isArray(value) && value.length === 0)) {
                return;
            }

            const label = labels[key] || key;
            const $wrapper = $('<div>').addClass('info-' + key);

            // Заголовок поля
            $('<div>')
                .addClass('banner-textblock-title')
                .text(label + ':')
                .appendTo($wrapper);

            // Значения
            const $valuesDiv = $('<div>').addClass('banner-textblock-values');
            
            if (Array.isArray(value)) {
                $.each(value, function(_, item) {
                    $valuesDiv.append($('<span>').text(item));
                });
            } else {
                $valuesDiv.append($('<span>').text(value));
            }

            $wrapper.append($valuesDiv);
            $textblock.append($wrapper);
        });

        // Смена фонового изображения с плавным переходом
        if (dataBg) {
            $banner.stop(true, true).fadeTo(200, 0.3, function() {
                $banner.css('background-image', 'url(' + dataBg + ')');
                $banner.fadeTo(200, 1);
            });
        }
    });

    /**
     * Обработчик ухода курсора с выпадающего блока
     * Восстанавливает исходное состояние баннера
     */
    $(document).on('mouseleave', '.mega-menu-inner', function() {
        const $megaMenu = $(this);
        const $banner = $megaMenu.find('.right-mega-banner');
        const $textblock = $banner.find('.banner-textblock');
        
        // Восстанавливаем оригинальное содержимое
        const originalHtml = $textblock.data('original');
        if (originalHtml) {
            $textblock.html(originalHtml);
        }

        // Восстанавливаем оригинальный фон (если был задан)
        const originalBg = $banner.data('original-bg');
        if (originalBg !== undefined) {
            $banner.stop(true, true).fadeTo(200, 0.3, function() {
                $banner.css('background-image', originalBg || 'none');
                $banner.fadeTo(200, 1);
            });
        }
    });

    /**
     * Сохраняем оригинальный фон баннера при загрузке страницы
     */
    $('.right-mega-banner').each(function() {
        const $banner = $(this);
        const originalBg = $banner.css('background-image');
        $banner.data('original-bg', originalBg);
    });
});
