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

    const MENU_LIGHT_CONTEXT_CLASS = 'menu-on-light-content';

    // Лейблы для отображения данных в баннере
    const labels = {
        fromnew: 'Стоимость',
        capacity: 'Вместимость',
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
        const hasInfo = !!dataInfo;

        // Пропускаем пустые элементы
        if ($item.hasClass('empty-list')) {
            return;
        }

        // Находим баннер и контейнер для текста
        const $banner = $item.closest('.mega-menu-inner').find('.right-mega-banner');
        const $textblock = $banner.find('.banner-textblock');

        // Рендерим текст только для пунктов, где есть data-info (места свадеб).
        if (hasInfo && $textblock.length) {
            // Сохраняем оригинальное содержимое при первом наведении
            if (!$textblock.data('original')) {
                $textblock.data('original', $textblock.html());
            }

            // Парсим данные места
            let data = {};
            try {
                data = JSON.parse(dataInfo);
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
        }

        // Для услуг затемнение мягче, чем для пунктов с текстовой информацией.
        if (dataBg) {
            if (hasInfo) {
                $banner.stop(true, true).fadeTo(200, 0.3, function() {
                    $banner.css('background-image', 'url(' + dataBg + ')');
                    $banner.fadeTo(200, 1);
                });
            } else {
                $banner.stop(true, true).fadeTo(200, 0.65, function() {
                    $banner.css('background-image', 'url(' + dataBg + ')');
                    $banner.fadeTo(200, 1);
                });
            }
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

    /**
     * Переключает тему стекла меню при выходе из hero-блока.
     * Пока hero находится под фиксированным меню — оставляем светлое стекло.
     * Когда hero заканчивается и начинается светлый контент — включаем темное стекло.
     */
    const hero = document.querySelector('.svadba-hero');
    const menu = document.querySelector('.svadba-hero-menu-center');
    const body = document.body;

    if (hero && menu && body && 'IntersectionObserver' in window) {
        let heroObserver = null;

        const setupHeroObserver = function() {
            const menuHeight = Math.ceil(menu.getBoundingClientRect().height || 0);

            if (heroObserver) {
                heroObserver.disconnect();
            }

            heroObserver = new IntersectionObserver(function(entries) {
                const entry = entries[0];
                if (!entry) {
                    return;
                }

                body.classList.toggle(MENU_LIGHT_CONTEXT_CLASS, !entry.isIntersecting);
            }, {
                root: null,
                threshold: 0,
                rootMargin: '-' + menuHeight + 'px 0px 0px 0px'
            });

            heroObserver.observe(hero);
        };

        setupHeroObserver();
        window.addEventListener('resize', setupHeroObserver);
    }
});
