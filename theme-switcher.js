// Theme Switcher - только для разработки
(function() {
    'use strict';
    
    // Создать переключатель при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        const switcher = document.createElement('div');
        switcher.className = 'theme-switcher';
        switcher.innerHTML = `
            <button data-theme="theme-default">По умолчанию</button>
            <button data-theme="theme-blue">Голубая</button>
            <button data-theme="theme-lavender">Лавандовая</button>
            <button data-theme="theme-green">Зелёная</button>
        `;
        
        document.body.appendChild(switcher);
        
        // Загрузить сохранённую тему
        const savedTheme = localStorage.getItem('dev-theme') || 'theme-default';
        document.body.className = savedTheme;
        
        // Обработчики кликов
        switcher.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', function() {
                const theme = this.dataset.theme;
                document.body.className = theme;
                localStorage.setItem('dev-theme', theme);
            });
        });
    });
})();
