/*
 * Параллакс фоновых слоёв секций-сценариев.
 *
 * Каждый .bw-fp-scenario__bg — абсолютно позиционированный div с фоновым
 * изображением, чуть больше родителя (inset:-4%) и масштабирован (scale 1.08),
 * чтобы при сдвиге края не были видны. На каждый кадр скролла вычисляем,
 * насколько центр секции смещён от центра вьюпорта, и двигаем фон на долю
 * этого расстояния — медленнее контента, что даёт иллюзию глубины.
 */
(function () {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var layers = document.querySelectorAll('.bw-fp-scenario__bg');
  if (!layers.length) return;

  var ticking = false;

  function update() {
    /*
     * Ниже 861px — мобильные. Параллакс отключаем: на тач-скролле
     * он тормозит, а секции занимают почти весь экран в высоту,
     * поэтому эффект всё равно почти не виден.
     */
    if (window.innerWidth < 861) {
      layers.forEach(function (l) { l.style.transform = 'none'; });
      return;
    }

    var vc = window.innerHeight / 2;

    layers.forEach(function (l) {
      var host = l.parentElement;
      if (!host) return;

      var rect = host.getBoundingClientRect();

      /*
       * dist — расстояние от центра секции до центра вьюпорта (px).
       * Скорость параллакса: 0.15 = фон движется в ~7 раз медленнее контента.
       * Знак минус: фон смещается в ту же сторону, что и секция, но медленнее.
       * scale(1.08) повторяет CSS-значение — JS-transform перезаписывает CSS.
       */
      var dist = (rect.top + rect.height / 2) - vc;
      var offset = -dist * 0.15;
      l.style.transform = 'translate3d(0,' + offset.toFixed(2) + 'px,0) scale(1.08)';
    });
  }

  function tick() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function () { update(); ticking = false; });
  }

  window.addEventListener('scroll', tick, { passive: true });
  window.addEventListener('resize', tick);
  tick();
}());

(function () {
  var watermarks = Array.prototype.slice.call(document.querySelectorAll('.bw-fp-watermark'));
  if (!watermarks.length) return;

  var ticking = false;

  function update() {
    var vh = window.innerHeight;
    watermarks.forEach(function (el, i) {
      var rect = el.getBoundingClientRect();
      // progress: 0 when element centre is at viewport centre
      var progress = (rect.top + rect.height / 2) - vh / 2;
      // alternate: even index → left-to-right, odd → right-to-left
      var dir = i % 2 === 0 ? 1 : -1;
      el.style.transform = 'translateX(' + (progress * 0.14 * dir).toFixed(2) + 'px)';
    });
    ticking = false;
  }

  window.addEventListener('scroll', function () {
    if (!ticking) {
      requestAnimationFrame(update);
      ticking = true;
    }
  }, { passive: true });

  update();
}());
