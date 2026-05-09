<?php
/**
 * Template Name: White Rabbit Animation
 * Description: Анимационные hero блоки и баннеры "Follow the White Rabbit"
 */

// Подключаем стили и скрипты
function enqueue_rabbit_assets() {
    $css_file = get_template_directory() . '/test-sample/rabbit-hero.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : wp_get_theme()->get('Version');
    wp_enqueue_style('rabbit-hero-styles', get_template_directory_uri() . '/test-sample/rabbit-hero.css', array('minimal-style'), $css_version);
    
    $js_file = get_template_directory() . '/test-sample/rabbit-hero.js';
    $js_version = file_exists($js_file) ? filemtime($js_file) : wp_get_theme()->get('Version');
    wp_enqueue_script('rabbit-hero-script', get_template_directory_uri() . '/test-sample/rabbit-hero.js', array('jquery'), $js_version, true);
}
add_action('wp_enqueue_scripts', 'enqueue_rabbit_assets');

get_header();
?>

<main class="rabbit-page layout">
  <div class="boxed">
    <header class="rabbit-page-header">
      <h1>Follow the White Rabbit</h1>
      <p>Анимационные hero блоки и баннеры</p>
    </header>

    <!-- Вариант 1: Анимированный SVG кролик с часами -->
    <section class="hero-variant">
      <h2>Вариант 1: SVG Анимация с часами</h2>
      <div class="hero hero-1">
        <div class="hero-bg">
          <div class="clock-background">
            <svg class="clock" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
              <circle cx="100" cy="100" r="90" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/>
              <circle cx="100" cy="100" r="80" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
              <!-- Цифры на циферблате -->
              <text x="100" y="35" text-anchor="middle" fill="rgba(255,255,255,0.3)" font-size="16" font-weight="bold">12</text>
              <text x="160" y="107" text-anchor="middle" fill="rgba(255,255,255,0.3)" font-size="16" font-weight="bold">3</text>
              <text x="100" y="180" text-anchor="middle" fill="rgba(255,255,255,0.3)" font-size="16" font-weight="bold">6</text>
              <text x="40" y="107" text-anchor="middle" fill="rgba(255,255,255,0.3)" font-size="16" font-weight="bold">9</text>
              <!-- Стрелки -->
              <line class="hour-hand" x1="100" y1="100" x2="100" y2="60" stroke="white" stroke-width="4" stroke-linecap="round"/>
              <line class="minute-hand" x1="100" y1="100" x2="100" y2="45" stroke="white" stroke-width="3" stroke-linecap="round"/>
              <line class="second-hand" x1="100" y1="100" x2="100" y2="35" stroke="#ff6b6b" stroke-width="2" stroke-linecap="round"/>
              <circle cx="100" cy="100" r="5" fill="white"/>
            </svg>
          </div>
        </div>
        
        <div class="hero-content">
          <div class="hero-text">
            <h1 class="glitch" data-text="Follow the White Rabbit">Follow the White Rabbit</h1>
            <p class="typewriter">I'm late! I'm late! For a very important date...</p>
            <button class="rabbit-btn">
              <span>Enter Wonderland</span>
              <span class="btn-arrow">→</span>
            </button>
          </div>
        </div>

        <!-- SVG Кролик -->
        <div class="rabbit-container rabbit-running">
          <svg class="rabbit-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <!-- Тело кролика -->
            <ellipse cx="50" cy="60" rx="20" ry="25" fill="white" class="rabbit-body"/>
            
            <!-- Голова -->
            <circle cx="50" cy="35" r="15" fill="white" class="rabbit-head"/>
            
            <!-- Уши -->
            <ellipse cx="43" cy="15" rx="5" ry="15" fill="white" class="rabbit-ear-left"/>
            <ellipse cx="57" cy="15" rx="5" ry="15" fill="white" class="rabbit-ear-right"/>
            <ellipse cx="43" cy="15" rx="3" ry="12" fill="pink" opacity="0.6"/>
            <ellipse cx="57" cy="15" rx="3" ry="12" fill="pink" opacity="0.6"/>
            
            <!-- Глаза -->
            <circle cx="45" cy="33" r="2" fill="black" class="rabbit-eye-left"/>
            <circle cx="55" cy="33" r="2" fill="black" class="rabbit-eye-right"/>
            
            <!-- Нос -->
            <circle cx="50" cy="38" r="1.5" fill="pink"/>
            
            <!-- Усы -->
            <line x1="40" y1="37" x2="30" y2="36" stroke="gray" stroke-width="0.5"/>
            <line x1="40" y1="39" x2="30" y2="40" stroke="gray" stroke-width="0.5"/>
            <line x1="60" y1="37" x2="70" y2="36" stroke="gray" stroke-width="0.5"/>
            <line x1="60" y1="39" x2="70" y2="40" stroke="gray" stroke-width="0.5"/>
            
            <!-- Передние лапы -->
            <ellipse cx="42" cy="75" rx="4" ry="8" fill="white" class="rabbit-leg-front-left"/>
            <ellipse cx="58" cy="75" rx="4" ry="8" fill="white" class="rabbit-leg-front-right"/>
            
            <!-- Задние лапы -->
            <ellipse cx="38" cy="80" rx="6" ry="5" fill="white" class="rabbit-leg-back-left"/>
            <ellipse cx="62" cy="80" rx="6" ry="5" fill="white" class="rabbit-leg-back-right"/>
            
            <!-- Хвост -->
            <circle cx="50" cy="85" r="5" fill="white" class="rabbit-tail"/>
            
            <!-- Жилет (опционально) -->
            <path d="M 45 50 Q 50 55 55 50 L 55 65 Q 50 68 45 65 Z" fill="#8b4513" opacity="0.8"/>
            <circle cx="50" cy="58" r="1.5" fill="gold"/>
          </svg>
          
          <!-- Карманные часы -->
          <svg class="pocket-watch" viewBox="0 0 40 50" xmlns="http://www.w3.org/2000/svg">
            <line x1="20" y1="0" x2="20" y2="10" stroke="gold" stroke-width="2"/>
            <circle cx="20" cy="10" r="3" fill="gold"/>
            <circle cx="20" cy="25" r="15" fill="gold" stroke="#8b4513" stroke-width="2"/>
            <circle cx="20" cy="25" r="13" fill="white"/>
            <line x1="20" y1="25" x2="20" y2="18" stroke="black" stroke-width="1.5"/>
            <line x1="20" y1="25" x2="25" y2="25" stroke="black" stroke-width="1"/>
          </svg>
        </div>
      </div>
    </section>

    <!-- Вариант 2: Кролик с CSS анимацией бега -->
    <section class="hero-variant">
      <h2>Вариант 2: CSS Run Animation</h2>
      <div class="hero hero-2">
        <div class="matrix-rain">
          <div class="matrix-column" style="left: 5%; animation-delay: 0s;"></div>
          <div class="matrix-column" style="left: 15%; animation-delay: 0.5s;"></div>
          <div class="matrix-column" style="left: 25%; animation-delay: 1s;"></div>
          <div class="matrix-column" style="left: 35%; animation-delay: 1.5s;"></div>
          <div class="matrix-column" style="left: 45%; animation-delay: 2s;"></div>
          <div class="matrix-column" style="left: 55%; animation-delay: 2.5s;"></div>
          <div class="matrix-column" style="left: 65%; animation-delay: 3s;"></div>
          <div class="matrix-column" style="left: 75%; animation-delay: 3.5s;"></div>
          <div class="matrix-column" style="left: 85%; animation-delay: 4s;"></div>
          <div class="matrix-column" style="left: 95%; animation-delay: 4.5s;"></div>
        </div>

        <div class="hero-content hero-centered">
          <div class="neon-text">
            <h1>FOLLOW</h1>
            <h1>THE WHITE</h1>
            <h1>RABBIT</h1>
          </div>
          <p class="hero-subtitle">Down the rabbit hole we go...</p>
          
          <div class="running-rabbit-track">
            <div class="running-rabbit">
              <div class="rabbit-sprite">
                <span class="rabbit-emoji">🐰</span>
              </div>
            </div>
          </div>
          
          <button class="matrix-btn">
            <span class="btn-text">Take the Red Pill</span>
            <span class="btn-glitch">Take the Red Pill</span>
          </button>
        </div>
      </div>
    </section>

    <!-- Вариант 3: Интерактивный баннер с кроликом по курсору -->
    <section class="hero-variant">
      <h2>Вариант 3: Interactive Rabbit Banner</h2>
      <div class="hero hero-3">
        <canvas id="particleCanvas" class="particle-canvas"></canvas>
        
        <div class="hero-content">
          <h1 class="hero-title-3">
            <span class="word">Curiosity</span>
            <span class="word">Leads</span>
            <span class="word">To</span>
            <span class="word">Discovery</span>
          </h1>
          
          <div class="rabbit-cursor-follower">
            <svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
              <g class="rabbit-simple">
                <ellipse cx="30" cy="35" rx="12" ry="15" fill="white"/>
                <circle cx="30" cy="22" r="10" fill="white"/>
                <ellipse cx="25" cy="10" rx="3" ry="10" fill="white"/>
                <ellipse cx="35" cy="10" rx="3" ry="10" fill="white"/>
                <circle cx="27" cy="21" r="1.5" fill="black"/>
                <circle cx="33" cy="21" r="1.5" fill="black"/>
                <circle cx="30" cy="24" r="1" fill="pink"/>
              </g>
            </svg>
          </div>
          
          <p class="tagline">Move your cursor and watch the magic happen</p>
          <button class="explore-btn">Start Your Journey</button>
        </div>
      </div>
    </section>

    <!-- Вариант 4: Кролик прыгает через текст -->
    <section class="hero-variant">
      <h2>Вариант 4: Jumping Rabbit</h2>
      <div class="hero hero-4">
        <div class="wonderland-bg">
          <div class="floating-card card-1">♠</div>
          <div class="floating-card card-2">♥</div>
          <div class="floating-card card-3">♦</div>
          <div class="floating-card card-4">♣</div>
          <div class="floating-card card-5">♠</div>
          <div class="floating-card card-6">♥</div>
        </div>
        
        <div class="hero-content">
          <div class="text-with-rabbit">
            <h1 class="jumping-text">
              <span class="letter">W</span>
              <span class="letter">O</span>
              <span class="letter">N</span>
              <span class="letter">D</span>
              <span class="letter">E</span>
              <span class="letter">R</span>
              <span class="letter">L</span>
              <span class="letter">A</span>
              <span class="letter">N</span>
              <span class="letter">D</span>
            </h1>
            
            <div class="jumping-rabbit">
              <svg viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="40" cy="50" rx="15" ry="20" fill="white"/>
                <circle cx="40" cy="30" r="12" fill="white"/>
                <ellipse cx="35" cy="15" rx="4" ry="12" fill="white"/>
                <ellipse cx="45" cy="15" rx="4" ry="12" fill="white"/>
                <circle cx="37" cy="29" r="2" fill="black"/>
                <circle cx="43" cy="29" r="2" fill="black"/>
                <path d="M 36 33 Q 40 35 44 33" stroke="black" fill="none" stroke-width="1"/>
              </svg>
            </div>
          </div>
          
          <p class="hero-description">Where imagination meets reality</p>
          <div class="cta-group">
            <button class="wonder-btn">Explore</button>
            <button class="wonder-btn-outline">Learn More</button>
          </div>
        </div>
      </div>
    </section>

  </div>
</main>

<?php get_footer(); ?>
