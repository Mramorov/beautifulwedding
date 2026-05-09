/**
 * Прайс-лист - переключение табов
 */

(function() {
    'use strict';
    
    // Ждем загрузки DOM
    document.addEventListener('DOMContentLoaded', function() {
        
        // Получаем все кнопки табов
        const tabButtons = document.querySelectorAll('.price-tabs-section .svadba-tab-button');
        const tabPanes = document.querySelectorAll('.price-tabs-section .svadba-tab-pane');
        
        if (!tabButtons.length || !tabPanes.length) {
            return;
        }
        
        // Обработчик клика по кнопке таба
        tabButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetTab = this.getAttribute('data-tab');
                
                // Убираем активный класс у всех кнопок
                tabButtons.forEach(function(btn) {
                    btn.classList.remove('active');
                });
                
                // Добавляем активный класс к текущей кнопке
                this.classList.add('active');
                
                // Скрываем все панели табов
                tabPanes.forEach(function(pane) {
                    pane.classList.remove('active');
                });
                
                // Показываем целевую панель
                const targetPane = document.getElementById(targetTab);
                if (targetPane) {
                    targetPane.classList.add('active');
                }
            });
        });
        
        // Поддержка навигации с клавиатуры
        tabButtons.forEach(function(button, index) {
            button.addEventListener('keydown', function(e) {
                let newIndex = index;
                
                // Стрелка влево
                if (e.keyCode === 37 && index > 0) {
                    newIndex = index - 1;
                }
                // Стрелка вправо
                else if (e.keyCode === 39 && index < tabButtons.length - 1) {
                    newIndex = index + 1;
                }
                
                if (newIndex !== index) {
                    e.preventDefault();
                    tabButtons[newIndex].click();
                    tabButtons[newIndex].focus();
                }
            });
        });
    });
    
})();
