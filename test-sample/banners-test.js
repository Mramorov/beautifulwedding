/**
 * Скрипты для интерактивности тестовых баннеров
 */

(function($) {
  'use strict';

  $(document).ready(function() {
    
    // Параллакс эффект для баннера 1
    $('.banner-1').on('mousemove', function(e) {
      const $banner = $(this);
      const $image = $banner.find('.banner-image');
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      const percentX = (x - centerX) / centerX;
      const percentY = (y - centerY) / centerY;
      
      $image.css({
        'transform': `scale(1.1) translate(${percentX * 20}px, ${percentY * 20}px)`
      });
    });
    
    $('.banner-1').on('mouseleave', function() {
      $(this).find('.banner-image').css({
        'transform': 'scale(1)'
      });
    });

    // Анимация появления контента для баннера 2
    let banner2Visible = false;
    const $banner2 = $('.banner-2');
    
    if ($banner2.length) {
      const observer2 = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !banner2Visible) {
            banner2Visible = true;
            const $content = $(entry.target).find('.banner-split-inner');
            $content.css('opacity', '0').animate({ opacity: 1 }, 800);
          }
        });
      }, { threshold: 0.3 });
      
      observer2.observe($banner2[0]);
    }

    // Эффект ripple для баннера 3
    $('.banner-3 .banner-card').on('click', function(e) {
      const $card = $(this);
      const $ripple = $('<span class="ripple"></span>');
      const x = e.clientX - $card.offset().left;
      const y = e.clientY - $card.offset().top;
      
      $ripple.css({
        left: x + 'px',
        top: y + 'px',
        position: 'absolute',
        width: '20px',
        height: '20px',
        borderRadius: '50%',
        background: 'rgba(255, 255, 255, 0.6)',
        transform: 'scale(0)',
        animation: 'ripple-animation 0.6s ease-out',
        pointerEvents: 'none'
      });
      
      $card.css('position', 'relative').append($ripple);
      
      setTimeout(() => {
        $ripple.remove();
      }, 600);
    });

    // Добавляем CSS для анимации ripple
    if (!$('#ripple-animation-style').length) {
      $('head').append(`
        <style id="ripple-animation-style">
          @keyframes ripple-animation {
            to {
              transform: scale(20);
              opacity: 0;
            }
          }
        </style>
      `);
    }

    // Плавное открытие оверлея для баннера 4
    $('.banner-4 .minimal-banner').on('mouseenter', function() {
      const $content = $(this).find('.minimal-content');
      $content.children().each(function(index) {
        const $child = $(this);
        setTimeout(() => {
          $child.css({
            'transform': 'translateY(0)',
            'opacity': '1'
          });
        }, index * 100);
      });
    });

    $('.banner-4 .minimal-banner').on('mouseleave', function() {
      $(this).find('.minimal-content').children().css({
        'transform': 'translateY(20px)',
        'opacity': '0'
      });
    });

    // Счетчик для звездочек рейтинга в баннере 5
    $('.banner-5 .shop-card').on('mouseenter', function() {
      const $stars = $(this).find('.stars');
      const originalStars = $stars.text();
      
      let count = 0;
      const interval = setInterval(() => {
        count++;
        $stars.css('color', count % 2 === 0 ? '#ffc107' : '#ffeb3b');
        
        if (count >= 3) {
          clearInterval(interval);
          $stars.css('color', '#ffc107');
        }
      }, 150);
    });

    // Анимация статистики для баннера 6
    const $banner6 = $('.banner-6');
    let banner6Animated = false;
    
    if ($banner6.length) {
      const observer6 = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !banner6Animated) {
            banner6Animated = true;
            
            $(entry.target).find('.stat-value').each(function() {
              const $stat = $(this);
              const text = $stat.text();
              const hasNumber = /\d+/.test(text);
              
              if (hasNumber) {
                const number = parseInt(text.match(/\d+/)[0]);
                const suffix = text.replace(/\d+/g, '').trim();
                let current = 0;
                const increment = number / 50;
                const duration = 1500;
                const stepTime = duration / 50;
                
                $stat.text('0 ' + suffix);
                
                const counter = setInterval(() => {
                  current += increment;
                  if (current >= number) {
                    $stat.text(number + ' ' + suffix);
                    clearInterval(counter);
                  } else {
                    $stat.text(Math.floor(current) + ' ' + suffix);
                  }
                }, stepTime);
              }
            });
          }
        });
      }, { threshold: 0.5 });
      
      observer6.observe($banner6[0]);
    }

    // Общая анимация появления для всех баннеров
    $('.banner-variant').each(function(index) {
      const $variant = $(this);
      $variant.css({
        'opacity': '0',
        'transform': 'translateY(30px)'
      });
      
      setTimeout(() => {
        $variant.animate({
          opacity: 1
        }, 600, function() {
          $(this).css('transform', 'translateY(0)');
        });
      }, index * 150);
    });

    // Lazy loading для изображений
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const $img = $(entry.target);
            const src = $img.attr('data-src');
            if (src) {
              $img.attr('src', src);
              $img.removeAttr('data-src');
            }
            imageObserver.unobserve(entry.target);
          }
        });
      });
      
      $('.banner img[data-src]').each(function() {
        imageObserver.observe(this);
      });
    }

    // Эффект cursor следа для кинематографичного баннера
    let cursorTrail = [];
    const maxTrailLength = 10;
    
    $('.banner-6').on('mousemove', function(e) {
      const $banner = $(this);
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      cursorTrail.push({ x, y });
      if (cursorTrail.length > maxTrailLength) {
        cursorTrail.shift();
      }
      
      // Визуальный эффект можно добавить позже
    });

    // Добавление класса при скролле для sticky эффектов
    let lastScrollTop = 0;
    $(window).on('scroll', function() {
      const scrollTop = $(this).scrollTop();
      
      $('.banner-variant').each(function() {
        const $variant = $(this);
        const variantTop = $variant.offset().top;
        const windowHeight = $(window).height();
        
        if (scrollTop + windowHeight > variantTop + 100) {
          $variant.addClass('is-visible');
        }
      });
      
      lastScrollTop = scrollTop;
    });

    console.log('✨ Тестовые баннеры инициализированы');
  });

})(jQuery);
