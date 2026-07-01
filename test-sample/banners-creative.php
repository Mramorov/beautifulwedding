<?php
/**
 * Template Name: Creative Banners Sample
 * Description: Необычные и креативные варианты баннеров для свадебных локаций
 */

// Подключаем стили и скрипты
function enqueue_creative_banners_assets() {
    $css_file = get_template_directory() . '/test-sample/banners-creative.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : wp_get_theme()->get('Version');
    wp_enqueue_style('creative-banners-styles', get_template_directory_uri() . '/test-sample/banners-creative.css', array('minimal-style'), $css_version);
    
    $js_file = get_template_directory() . '/test-sample/banners-creative.js';
    $js_version = file_exists($js_file) ? filemtime($js_file) : wp_get_theme()->get('Version');
    wp_enqueue_script('creative-banners-script', get_template_directory_uri() . '/test-sample/banners-creative.js', array('jquery'), $js_version, true);
}
add_action('wp_enqueue_scripts', 'enqueue_creative_banners_assets');

get_header();

// Получаем данные тестового поста
$test_post_id = 84;
$post = get_post($test_post_id);
$title = get_the_title($test_post_id);
$excerpt = get_the_excerpt($test_post_id);
$permalink = get_permalink($test_post_id);
$thumbnail_url = get_the_post_thumbnail_url($test_post_id, 'large');
$thumbnail_full = get_the_post_thumbnail_url($test_post_id, 'full');

// Мета-данные
$fromnew = get_post_meta($test_post_id, 'fromnew', true);
$capacity = get_post_meta($test_post_id, 'capacity', true);
$cer_time = get_post_meta($test_post_id, 'cer-time', true);
?>

<main class="creative-banners-page layout">
  <div class="boxed">
    <header class="creative-header">
      <h1>Креативные баннеры для свадебных локаций</h1>
      <p>Необычные и запоминающиеся решения</p>
    </header>

    <!-- Вариант 1: 3D Tilt Card (эффект наклона при движении мыши) -->
    <section class="banner-variant">
      <h2>Вариант 1: 3D Tilt Card</h2>
      <div class="banner creative-banner-1">
        <div class="tilt-card" data-tilt>
          <a href="<?php echo esc_url($permalink); ?>" class="tilt-link">
            <div class="tilt-card-inner">
              <div class="tilt-bg" style="background-image: url('<?php echo esc_url($thumbnail_full); ?>');"></div>
              <div class="tilt-shine"></div>
              <div class="tilt-content">
                <div class="tilt-badge">Премиум</div>
                <h3 class="tilt-title"><?php echo esc_html($title); ?></h3>
                <p class="tilt-description"><?php echo esc_html($excerpt); ?></p>
                <div class="tilt-meta">
                  <?php if ($fromnew): ?>
                    <span class="tilt-price">от <?php echo esc_html($fromnew); ?> €</span>
                  <?php endif; ?>
                  <?php if ($capacity): ?>
                    <span class="tilt-capacity"><?php echo esc_html($capacity); ?> гостей</span>
                  <?php endif; ?>
                </div>
                <button class="tilt-btn">
                  <span>Забронировать</span>
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M4 10h12m0 0l-5-5m5 5l-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
            </div>
          </a>
        </div>
      </div>
    </section>

    <!-- Вариант 2: Morphing Card (трансформация при наведении) -->
    <section class="banner-variant">
      <h2>Вариант 2: Morphing Card</h2>
      <div class="banner creative-banner-2">
        <a href="<?php echo esc_url($permalink); ?>" class="morph-card">
          <div class="morph-front">
            <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($title); ?>" class="morph-image">
            <div class="morph-overlay">
              <h3 class="morph-title"><?php echo esc_html($title); ?></h3>
              <p class="morph-hint">Наведите для подробностей</p>
            </div>
          </div>
          <div class="morph-back">
            <div class="morph-back-content">
              <h3 class="morph-back-title"><?php echo esc_html($title); ?></h3>
              <p class="morph-back-description"><?php echo esc_html($excerpt); ?></p>
              <ul class="morph-features">
                <?php if ($fromnew): ?>
                  <li>💰 от <?php echo esc_html($fromnew); ?> €</li>
                <?php endif; ?>
                <?php if ($capacity): ?>
                  <li>👥 до <?php echo esc_html($capacity); ?> гостей</li>
                <?php endif; ?>
                <?php if ($cer_time): ?>
                  <li>⏰ <?php echo esc_html($cer_time); ?></li>
                <?php endif; ?>
                <li>🎭 Церемония и банкет</li>
                <li>📸 Фотозоны</li>
              </ul>
              <span class="morph-cta">Узнать больше →</span>
            </div>
          </div>
        </a>
      </div>
    </section>

    <!-- Вариант 3: Glassmorphism с анимированными пузырьками -->
    <section class="banner-variant">
      <h2>Вариант 3: Glassmorphism</h2>
      <div class="banner creative-banner-3">
        <a href="<?php echo esc_url($permalink); ?>" class="glass-banner">
          <div class="glass-bg" style="background-image: url('<?php echo esc_url($thumbnail_full); ?>');"></div>
          <div class="glass-bubbles">
            <span class="bubble"></span>
            <span class="bubble"></span>
            <span class="bubble"></span>
            <span class="bubble"></span>
            <span class="bubble"></span>
          </div>
          <div class="glass-card">
            <div class="glass-header">
              <span class="glass-label">Свадебная локация</span>
              <span class="glass-status">Доступна</span>
            </div>
            <h3 class="glass-title"><?php echo esc_html($title); ?></h3>
            <p class="glass-description"><?php echo esc_html($excerpt); ?></p>
            <div class="glass-stats">
              <?php if ($capacity): ?>
                <div class="glass-stat">
                  <div class="glass-stat-icon">👥</div>
                  <div class="glass-stat-info">
                    <span class="glass-stat-label">Вместимость</span>
                    <span class="glass-stat-value"><?php echo esc_html($capacity); ?></span>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($fromnew): ?>
                <div class="glass-stat">
                  <div class="glass-stat-icon">💎</div>
                  <div class="glass-stat-info">
                    <span class="glass-stat-label">Стоимость</span>
                    <span class="glass-stat-value">от <?php echo esc_html($fromnew); ?> €</span>
                  </div>
                </div>
              <?php endif; ?>
            </div>
            <button class="glass-btn">
              <span>Выбрать локацию</span>
            </button>
          </div>
        </a>
      </div>
    </section>

    <!-- Вариант 4: Разделенный баннер с анимацией -->
    <section class="banner-variant">
      <h2>Вариант 4: Split Reveal</h2>
      <div class="banner creative-banner-4">
        <a href="<?php echo esc_url($permalink); ?>" class="split-banner">
          <div class="split-left">
            <div class="split-image" style="background-image: url('<?php echo esc_url($thumbnail_full); ?>');"></div>
            <div class="split-number">01</div>
          </div>
          <div class="split-right">
            <div class="split-content">
              <div class="split-top">
                <span class="split-category">Premium Location</span>
                <h3 class="split-title"><?php echo esc_html($title); ?></h3>
              </div>
              <div class="split-middle">
                <p class="split-text"><?php echo esc_html($excerpt); ?></p>
                <div class="split-divider"></div>
                <div class="split-info-grid">
                  <?php if ($capacity): ?>
                    <div class="split-info-item">
                      <span class="split-info-label">Гости</span>
                      <span class="split-info-value"><?php echo esc_html($capacity); ?></span>
                    </div>
                  <?php endif; ?>
                  <?php if ($fromnew): ?>
                    <div class="split-info-item">
                      <span class="split-info-label">от</span>
                      <span class="split-info-value"><?php echo esc_html($fromnew); ?> €</span>
                    </div>
                  <?php endif; ?>
                  <?php if ($cer_time): ?>
                    <div class="split-info-item">
                      <span class="split-info-label">Время</span>
                      <span class="split-info-value"><?php echo esc_html($cer_time); ?></span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="split-bottom">
                <button class="split-btn">
                  <span>Подробнее</span>
                  <div class="split-btn-bg"></div>
                </button>
              </div>
            </div>
          </div>
        </a>
      </div>
    </section>

    <!-- Вариант 5: Neon / Cyberpunk Style -->
    <section class="banner-variant">
      <h2>Вариант 5: Neon Style</h2>
      <div class="banner creative-banner-5">
        <a href="<?php echo esc_url($permalink); ?>" class="neon-banner">
          <div class="neon-bg" style="background-image: url('<?php echo esc_url($thumbnail_full); ?>');"></div>
          <div class="neon-grid"></div>
          <div class="neon-content">
            <div class="neon-tag">PREMIUM</div>
            <h3 class="neon-title" data-text="<?php echo esc_attr($title); ?>"><?php echo esc_html($title); ?></h3>
            <p class="neon-description"><?php echo esc_html($excerpt); ?></p>
            <div class="neon-stats-bar">
              <?php if ($capacity): ?>
                <div class="neon-stat">
                  <span class="neon-stat-icon">■</span>
                  <span><?php echo esc_html($capacity); ?> GUESTS</span>
                </div>
              <?php endif; ?>
              <?php if ($fromnew): ?>
                <div class="neon-stat">
                  <span class="neon-stat-icon">■</span>
                  <span>FROM <?php echo esc_html($fromnew); ?> €</span>
                </div>
              <?php endif; ?>
            </div>
            <button class="neon-btn">
              <span class="neon-btn-text">BOOK NOW</span>
              <span class="neon-btn-glow"></span>
            </button>
          </div>
          <div class="neon-corners">
            <span class="neon-corner neon-corner-tl"></span>
            <span class="neon-corner neon-corner-tr"></span>
            <span class="neon-corner neon-corner-bl"></span>
            <span class="neon-corner neon-corner-br"></span>
          </div>
        </a>
      </div>
    </section>

  </div>
</main>

<?php get_footer(); ?>
