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
