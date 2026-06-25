(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.svadba-tabs-nav').forEach(function (nav) {
            var buttons = Array.prototype.slice.call(nav.querySelectorAll('.svadba-tab-button'));
            if (!buttons.length) return;

            var content = nav.parentElement && nav.parentElement.querySelector('.svadba-tabs-content');

            function activateTab(targetId) {
                if (!targetId) return;
                buttons.forEach(function (b) { b.classList.remove('active'); });
                if (content) {
                    content.querySelectorAll('.svadba-tab-pane').forEach(function (p) { p.classList.remove('active'); });
                }
                buttons.forEach(function (b) {
                    if (b.getAttribute('data-tab') === targetId) b.classList.add('active');
                });
                var pane = document.getElementById(targetId);
                if (pane) pane.classList.add('active');
            }

            buttons.forEach(function (button, index) {
                button.addEventListener('click', function () {
                    activateTab(this.getAttribute('data-tab'));
                });

                button.addEventListener('keydown', function (e) {
                    var newIndex = index;
                    if (e.keyCode === 37 && index > 0) newIndex = index - 1;
                    else if (e.keyCode === 39 && index < buttons.length - 1) newIndex = index + 1;
                    if (newIndex !== index) {
                        e.preventDefault();
                        buttons[newIndex].click();
                        buttons[newIndex].focus();
                    }
                });
            });
        });
    });
})();
