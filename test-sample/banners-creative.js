/**
 * JavaScript для креативных интерактивных баннеров
 */

(function($) {
  'use strict';

  $(document).ready(function() {
    
    // ============================================
    // ВАРИАНТ 1: 3D TILT ЭФФЕКТ
    // ============================================
    
    $('.tilt-card').each(function() {
      const $card = $(this);
      const $inner = $card.find('.tilt-card-inner');
      
      $card.on('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = (y - centerY) / 10;
        const rotateY = (centerX - x) / 10;
        
        $card.css({
          'transform': `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.05, 1.05, 1.05)`
        });
        
        // Эффект блеска
        const shineX = (x / rect.width) * 100;
        const shineY = (y / rect.height) * 100;
        $card.find('.tilt-shine').css({
          'background': `radial-gradient(circle at ${shineX}% ${shineY}%, rgba(255,255,255,0.3) 0%, transparent 60%)`
        });
      });
      
      $card.on('mouseleave', function() {
        $card.css({
          'transform': 'rotateX(0) rotateY(0) scale3d(1, 1, 1)'
        });
      });
    });

    // ============================================
    // ВАРИАНТ 2: MORPHING CARD (уже работает через CSS)
    // Добавим звуковой эффект при переворачивании (опционально)
    // ============================================
    
    let isMorphed = false;
    $('.morph-card').on('mouseenter', function() {
      if (!isMorphed) {
        isMorphed = true;
        // Можно добавить аудио эффект
        console.log('Card morphed');
      }
    }).on('mouseleave', function() {
      isMorphed = false;
    });

    // ============================================
    // ВАРИАНТ 3: GLASSMORPHISM
    // Добавляем интерактивность пузырькам
    // ============================================
    
    $('.glass-banner').on('mousemove', function(e) {
      const $bubbles = $(this).find('.bubble');
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      $bubbles.each(function(index) {
        const $bubble = $(this);
        const bubbleX = parseFloat($bubble.css('left'));
        const bubbleY = parseFloat($bubble.css('bottom'));
        const distance = Math.sqrt(Math.pow(x - bubbleX, 2) + Math.pow(y - (rect.height - bubbleY), 2));
        
        if (distance < 100) {
          const scale = 1 + (100 - distance) / 100;
          $bubble.css({
            'transform': `scale(${scale})`,
            'opacity': Math.min(1, (100 - distance) / 50)
          });
        } else {
          $bubble.css({
            'transform': 'scale(1)',
            'opacity': 0.3
          });
        }
      });
    });
    
    $('.glass-banner').on('mouseleave', function() {
      $(this).find('.bubble').css({
        'transform': 'scale(1)',
        'opacity': 0.3
      });
    });

    // ============================================
    // ВАРИАНТ 4: SPLIT REVEAL
    // Добавим прогресс-бар при наведении
    // ============================================
    
    $('.split-banner').each(function() {
      const $banner = $(this);
      
      $banner.on('mouseenter', function() {
        // Анимация появления элементов
        const $items = $(this).find('.split-info-item');
        $items.each(function(index) {
          const $item = $(this);
          setTimeout(() => {
            $item.css({
              'transform': 'translateY(0)',
              'opacity': '1'
            });
          }, index * 100);
        });
      });
      
      $banner.on('mouseleave', function() {
        $(this).find('.split-info-item').css({
          'transform': 'translateY(10px)',
          'opacity': '0'
        });
      });
    });
    
    // Инициализируем начальное состояние
    $('.split-info-item').css({
      'transform': 'translateY(10px)',
      'opacity': '0',
      'transition': 'all 0.3s ease'
    });

    // ============================================
    // ВАРИАНТ 5: NEON / CYBERPUNK
    // Добавляем glitch эффект при наведении
    // ============================================
    
    $('.neon-banner').on('mouseenter', function() {
      const $title = $(this).find('.neon-title');
      
      // Glitch эффект
      let glitchInterval = setInterval(() => {
        const randomOffset = Math.random() * 4 - 2;
        $title.css({
          'transform': `translateX(${randomOffset}px)`,
          'text-shadow': `
            ${randomOffset}px 0 2px rgba(255, 0, 0, 0.5),
            ${-randomOffset}px 0 2px rgba(0, 255, 0, 0.5),
            0 0 10px rgba(0, 255, 136, 0.5),
            0 0 20px rgba(0, 255, 136, 0.3)
          `
        });
      }, 100);
      
      $(this).data('glitchInterval', glitchInterval);
      
      setTimeout(() => {
        clearInterval(glitchInterval);
        $title.css({
          'transform': 'translateX(0)',
          'text-shadow': `
            0 0 10px rgba(0, 255, 136, 0.5),
            0 0 20px rgba(0, 255, 136, 0.3),
            0 0 30px rgba(0, 255, 136, 0.2)
          `
        });
      }, 500);
    });
    
    $('.neon-banner').on('mouseleave', function() {
      const glitchInterval = $(this).data('glitchInterval');
      if (glitchInterval) {
        clearInterval(glitchInterval);
      }
    });
    
    // Сканирующая линия для neon баннера
    function createScanLine() {
      const $scanLine = $('<div class="scan-line"></div>');
      $scanLine.css({
        'position': 'absolute',
        'top': '0',
        'left': '0',
        'width': '100%',
        'height': '2px',
        'background': 'rgba(0, 255, 136, 0.5)',
        'box-shadow': '0 0 10px rgba(0, 255, 136, 0.8)',
        'z-index': '3',
        'animation': 'scan 3s linear infinite'
      });
      
      $('.neon-banner').append($scanLine);
    }
    
    createScanLine();
    
    // Добавляем CSS анимацию для сканирующей линии
    if (!$('#scan-animation-style').length) {
      $('head').append(`
        <style id="scan-animation-style">
          @keyframes scan {
            0% { top: 0; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
          }
        </style>
      `);
    }

    // ============================================
    // ОБЩИЕ ЭФФЕКТЫ
    // ============================================
    
    // Intersection Observer для анимации появления
    if ('IntersectionObserver' in window) {
      const bannerObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            $(entry.target).addClass('animate-in');
            bannerObserver.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.2,
        rootMargin: '0px 0px -100px 0px'
      });
      
      $('.banner').each(function() {
        $(this).css({
          'opacity': '0',
          'transform': 'translateY(50px)',
          'transition': 'opacity 0.8s ease, transform 0.8s ease'
        });
        bannerObserver.observe(this);
      });
      
      // CSS для анимации появления
      if (!$('#animate-in-style').length) {
        $('head').append(`
          <style id="animate-in-style">
            .banner.animate-in {
              opacity: 1 !important;
              transform: translateY(0) !important;
            }
          </style>
        `);
      }
    }

    // Курсор-трейлер для креативных баннеров
    let cursorTrail = [];
    const maxTrailLength = 20;
    
    $('.creative-banner-1, .creative-banner-5').on('mousemove', function(e) {
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      cursorTrail.push({ x, y, time: Date.now() });
      
      if (cursorTrail.length > maxTrailLength) {
        cursorTrail.shift();
      }
    });

    // Particle эффект при клике на баннеры
    $('.banner a').on('click', function(e) {
      const $link = $(this);
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      // Создаем частицы
      for (let i = 0; i < 12; i++) {
        const $particle = $('<div class="click-particle"></div>');
        const angle = (Math.PI * 2 * i) / 12;
        const velocity = 100;
        const vx = Math.cos(angle) * velocity;
        const vy = Math.sin(angle) * velocity;
        
        $particle.css({
          'position': 'absolute',
          'left': x + 'px',
          'top': y + 'px',
          'width': '8px',
          'height': '8px',
          'background': 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          'border-radius': '50%',
          'pointer-events': 'none',
          'z-index': '1000',
          'opacity': '1',
          'transform': 'translate(-50%, -50%)'
        });
        
        $link.append($particle);
        
        // Анимируем частицу
        $particle.animate({
          left: (x + vx) + 'px',
          top: (y + vy) + 'px',
          opacity: 0
        }, 800, 'easeOutQuad', function() {
          $(this).remove();
        });
      }
    });

    // Добавляем easing функцию
    $.extend($.easing, {
      easeOutQuad: function(x) {
        return 1 - (1 - x) * (1 - x);
      }
    });

    // Плавная прокрутка при переходе между баннерами
    $('.banner-variant h2').on('click', function() {
      const $variant = $(this).parent();
      $('html, body').animate({
        scrollTop: $variant.offset().top - 100
      }, 600);
    });

    // Счетчик FPS для отладки (опционально)
    let lastTime = performance.now();
    let frames = 0;
    
    function countFPS() {
      const currentTime = performance.now();
      frames++;
      
      if (currentTime >= lastTime + 1000) {
        const fps = Math.round((frames * 1000) / (currentTime - lastTime));
        console.log('FPS:', fps);
        frames = 0;
        lastTime = currentTime;
      }
      
      requestAnimationFrame(countFPS);
    }
    
    // Раскомментируйте для отладки производительности
    // countFPS();

    console.log('🎨 Креативные баннеры инициализированы');
  });

})(jQuery);
