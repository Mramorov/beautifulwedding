/**
 * JavaScript для анимации "Follow the White Rabbit"
 */

(function($) {
  'use strict';

  $(document).ready(function() {
    
    // ============================================
    // ВАРИАНТ 3: ИНТЕРАКТИВНЫЙ КРОЛИК ЗА КУРСОРОМ
    // ============================================
    
    const $rabbitFollower = $('.rabbit-cursor-follower');
    let mouseX = 0;
    let mouseY = 0;
    let rabbitX = 0;
    let rabbitY = 0;
    
    $('.hero-3').on('mousemove', function(e) {
      mouseX = e.clientX;
      mouseY = e.clientY;
    });
    
    function animateRabbit() {
      // Плавное следование за курсором
      rabbitX += (mouseX - rabbitX) * 0.1;
      rabbitY += (mouseY - rabbitY) * 0.1;
      
      $rabbitFollower.css({
        left: rabbitX - 30 + 'px',
        top: rabbitY - 30 + 'px'
      });
      
      requestAnimationFrame(animateRabbit);
    }
    
    if ($rabbitFollower.length) {
      animateRabbit();
    }
    
    // ============================================
    // CANVAS PARTICLES
    // ============================================
    
    const canvas = document.getElementById('particleCanvas');
    if (canvas) {
      const ctx = canvas.getContext('2d');
      let particles = [];
      
      function resizeCanvas() {
        canvas.width = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
      }
      
      resizeCanvas();
      window.addEventListener('resize', resizeCanvas);
      
      class Particle {
        constructor() {
          this.x = Math.random() * canvas.width;
          this.y = Math.random() * canvas.height;
          this.size = Math.random() * 3 + 1;
          this.speedX = Math.random() * 2 - 1;
          this.speedY = Math.random() * 2 - 1;
          this.opacity = Math.random() * 0.5 + 0.2;
        }
        
        update() {
          this.x += this.speedX;
          this.y += this.speedY;
          
          if (this.x > canvas.width || this.x < 0) {
            this.speedX *= -1;
          }
          
          if (this.y > canvas.height || this.y < 0) {
            this.speedY *= -1;
          }
        }
        
        draw() {
          ctx.fillStyle = `rgba(255, 255, 255, ${this.opacity})`;
          ctx.beginPath();
          ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
          ctx.fill();
        }
      }
      
      function initParticles() {
        particles = [];
        const particleCount = Math.floor((canvas.width * canvas.height) / 10000);
        for (let i = 0; i < particleCount; i++) {
          particles.push(new Particle());
        }
      }
      
      function animateParticles() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        particles.forEach(particle => {
          particle.update();
          particle.draw();
        });
        
        // Соединяем близкие частицы
        connectParticles();
        
        requestAnimationFrame(animateParticles);
      }
      
      function connectParticles() {
        for (let i = 0; i < particles.length; i++) {
          for (let j = i + 1; j < particles.length; j++) {
            const dx = particles[i].x - particles[j].x;
            const dy = particles[i].y - particles[j].y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            if (distance < 100) {
              ctx.strokeStyle = `rgba(255, 255, 255, ${0.15 * (1 - distance / 100)})`;
              ctx.lineWidth = 1;
              ctx.beginPath();
              ctx.moveTo(particles[i].x, particles[i].y);
              ctx.lineTo(particles[j].x, particles[j].y);
              ctx.stroke();
            }
          }
        }
      }
      
      initParticles();
      animateParticles();
    }
    
    // ============================================
    // TYPEWRITER ЭФФЕКТ
    // ============================================
    
    const $typewriter = $('.typewriter');
    if ($typewriter.length) {
      const text = $typewriter.text();
      $typewriter.text('');
      $typewriter.css({
        'border-right': '3px solid white',
        'white-space': 'nowrap',
        'overflow': 'hidden'
      });
      
      let charIndex = 0;
      
      function type() {
        if (charIndex < text.length) {
          $typewriter.text($typewriter.text() + text.charAt(charIndex));
          charIndex++;
          setTimeout(type, 100);
        } else {
          // Мигающий курсор
          setInterval(() => {
            const currentBorder = $typewriter.css('border-right-color');
            $typewriter.css('border-right-color', 
              currentBorder === 'rgba(0, 0, 0, 0)' || currentBorder === 'transparent' 
                ? 'white' 
                : 'transparent'
            );
          }, 500);
        }
      }
      
      setTimeout(type, 1000);
    }
    
    // ============================================
    // GLITCH ЭФФЕКТ ПРИ НАВЕДЕНИИ
    // ============================================
    
    $('.glitch').on('mouseenter', function() {
      const $this = $(this);
      let glitchCount = 0;
      
      const glitchInterval = setInterval(() => {
        $this.css({
          'transform': `translate(${Math.random() * 4 - 2}px, ${Math.random() * 4 - 2}px)`
        });
        
        glitchCount++;
        if (glitchCount > 10) {
          clearInterval(glitchInterval);
          $this.css('transform', 'translate(0, 0)');
        }
      }, 50);
    });
    
    // ============================================
    // ПОЯВЛЕНИЕ БУКВ ПО ОЧЕРЕДИ
    // ============================================
    
    const observerOptions = {
      threshold: 0.5,
      rootMargin: '0px'
    };
    
    const letterObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const $letters = $(entry.target).find('.letter');
          $letters.each(function(index) {
            setTimeout(() => {
              $(this).css({
                'opacity': '1',
                'transform': 'translateY(0)'
              });
            }, index * 100);
          });
          letterObserver.unobserve(entry.target);
        }
      });
    }, observerOptions);
    
    $('.jumping-text').each(function() {
      // Устанавливаем начальное состояние
      $(this).find('.letter').css({
        'opacity': '0',
        'transform': 'translateY(50px)',
        'transition': 'all 0.5s ease',
        'display': 'inline-block'
      });
      letterObserver.observe(this);
    });
    
    // ============================================
    // MATRIX RAIN ГЕНЕРАТОР
    // ============================================
    
    function createMatrixCode() {
      const matrixChars = '01アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン';
      
      $('.matrix-column').each(function() {
        const $column = $(this);
        let codeString = '';
        
        for (let i = 0; i < 30; i++) {
          const char = matrixChars.charAt(Math.floor(Math.random() * matrixChars.length));
          codeString += char + '\n';
        }
        
        $column.text(codeString);
        $column.css({
          'white-space': 'pre',
          'font-family': 'monospace',
          'font-size': '14px',
          'color': '#0f0',
          'line-height': '20px'
        });
      });
    }
    
    if ($('.matrix-column').length) {
      createMatrixCode();
      
      // Обновляем коды каждые 2 секунды
      setInterval(createMatrixCode, 2000);
    }
    
    // ============================================
    // КРОЛИК - СЛУЧАЙНОЕ НАПРАВЛЕНИЕ
    // ============================================
    
    let rabbitDirection = 1;
    
    $('.rabbit-container').on('animationiteration', function() {
      // Меняем направление случайным образом
      if (Math.random() > 0.7) {
        rabbitDirection *= -1;
        $(this).css('transform', `scaleX(${rabbitDirection})`);
      }
    });
    
    // ============================================
    // ИНТЕРАКТИВНЫЕ КНОПКИ
    // ============================================
    
    $('.rabbit-btn, .explore-btn, .wonder-btn, .wonder-btn-outline').on('click', function(e) {
      const $btn = $(this);
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      // Создаем ripple эффект
      const $ripple = $('<span class="ripple-effect"></span>');
      $ripple.css({
        position: 'absolute',
        left: x + 'px',
        top: y + 'px',
        width: '0',
        height: '0',
        borderRadius: '50%',
        background: 'rgba(255, 255, 255, 0.5)',
        transform: 'translate(-50%, -50%)',
        animation: 'ripple-expand 0.6s ease-out'
      });
      
      $btn.css('position', 'relative').css('overflow', 'hidden').append($ripple);
      
      setTimeout(() => {
        $ripple.remove();
      }, 600);
    });
    
    // Добавляем CSS для ripple
    if (!$('#ripple-expand-style').length) {
      $('head').append(`
        <style id="ripple-expand-style">
          @keyframes ripple-expand {
            to {
              width: 300px;
              height: 300px;
              opacity: 0;
            }
          }
        </style>
      `);
    }
    
    // ============================================
    // СЛЕДЫ ЗА КРОЛИКОМ
    // ============================================
    
    const $runningRabbit = $('.running-rabbit');
    
    if ($runningRabbit.length) {
      setInterval(() => {
        const rabbitPos = $runningRabbit.offset();
        const $footprint = $('<div class="rabbit-footprint">🐾</div>');
        
        $footprint.css({
          position: 'fixed',
          left: (rabbitPos?.left || 0) + 'px',
          top: (rabbitPos?.top || 0) + 60 + 'px',
          fontSize: '20px',
          opacity: '0.6',
          pointerEvents: 'none',
          zIndex: '1'
        });
        
        $('body').append($footprint);
        
        $footprint.animate({
          opacity: 0,
          top: '+=' + 20
        }, 2000, function() {
          $(this).remove();
        });
      }, 500);
    }
    
    // ============================================
    // ЗВУКОВЫЕ ЭФФЕКТЫ (опционально)
    // ============================================
    
    // Можно добавить звуки при взаимодействии
    const sounds = {
      hop: new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhByx+z/LZiToJFmi79+egTA8MSKXh8…') // Упрощенный пример
    };
    
    // ============================================
    // ЭФФЕКТЫ ПРИ ПРОКРУТКЕ
    // ============================================
    
    const heroObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          $(entry.target).addClass('hero-visible');
        }
      });
    }, {
      threshold: 0.3
    });
    
    $('.hero').each(function() {
      $(this).css({
        'opacity': '0',
        'transform': 'scale(0.95)',
        'transition': 'opacity 0.8s ease, transform 0.8s ease'
      });
      heroObserver.observe(this);
    });
    
    if (!$('#hero-visible-style').length) {
      $('head').append(`
        <style id="hero-visible-style">
          .hero.hero-visible {
            opacity: 1 !important;
            transform: scale(1) !important;
          }
        </style>
      `);
    }
    
    // ============================================
    // ПАСХАЛКА: KONAMI CODE
    // ============================================
    
    let konamiCode = [];
    const konamiSequence = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65]; // ↑↑↓↓←→←→BA
    
    $(document).on('keydown', function(e) {
      konamiCode.push(e.keyCode);
      
      if (konamiCode.length > konamiSequence.length) {
        konamiCode.shift();
      }
      
      if (JSON.stringify(konamiCode) === JSON.stringify(konamiSequence)) {
        activateEasterEgg();
        konamiCode = [];
      }
    });
    
    function activateEasterEgg() {
      // Создаем кучу кроликов!
      for (let i = 0; i < 20; i++) {
        setTimeout(() => {
          const $easterRabbit = $('<div class="easter-rabbit">🐰</div>');
          $easterRabbit.css({
            position: 'fixed',
            left: Math.random() * 100 + '%',
            top: '-50px',
            fontSize: '40px',
            zIndex: '10000',
            animation: 'fall-down 3s linear forwards'
          });
          
          $('body').append($easterRabbit);
          
          setTimeout(() => {
            $easterRabbit.remove();
          }, 3000);
        }, i * 200);
      }
      
      // Показываем сообщение
      const $message = $('<div class="konami-message">🎉 You found the White Rabbit! 🐰</div>');
      $message.css({
        position: 'fixed',
        top: '50%',
        left: '50%',
        transform: 'translate(-50%, -50%)',
        background: 'rgba(0, 0, 0, 0.9)',
        color: 'white',
        padding: '30px 50px',
        borderRadius: '15px',
        fontSize: '24px',
        fontWeight: 'bold',
        zIndex: '10001',
        boxShadow: '0 10px 50px rgba(255, 255, 255, 0.3)'
      });
      
      $('body').append($message);
      
      setTimeout(() => {
        $message.fadeOut(1000, function() {
          $(this).remove();
        });
      }, 3000);
    }
    
    // Добавляем CSS для падения
    if (!$('#fall-down-style').length) {
      $('head').append(`
        <style id="fall-down-style">
          @keyframes fall-down {
            to {
              top: 100%;
              transform: rotate(360deg);
            }
          }
        </style>
      `);
    }
    
    console.log('🐰 Follow the White Rabbit - Initialized');
    console.log('💡 Hint: Try the Konami Code!');
  });

})(jQuery);
