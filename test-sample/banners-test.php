<?php
/**
 * Template Name: Test Banners Sample
 * Description: Тестовые варианты баннеров для свадебных локаций
 */

// Подключаем стили и скрипты
function enqueue_test_banners_assets() {
    $css_file = get_template_directory() . '/test-sample/banners-test.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : wp_get_theme()->get('Version');
    wp_enqueue_style('test-banners-styles', get_template_directory_uri() . '/test-sample/banners-test.css', array('minimal-style'), $css_version);
    
    $js_file = get_template_directory() . '/test-sample/banners-test.js';
    $js_version = file_exists($js_file) ? filemtime($js_file) : wp_get_theme()->get('Version');
    wp_enqueue_script('test-banners-script', get_template_directory_uri() . '/test-sample/banners-test.js', array('jquery'), $js_version, true);
}
add_action('wp_enqueue_scripts', 'enqueue_test_banners_assets');

get_header();

// Получаем данные тестового поста (свадебной локации)
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

<main class="test-banners-page layout">
  <div class="boxed">
    <header class="test-header">
      <h1>Тестовые варианты баннеров свадебных локаций</h1>
      <p>6 современных стилей для главной страницы</p>
    </header>

    <!-- Вариант 1: Классический оверлей с градиентом -->
    <section class="banner-variant">
      <h2>Вариант 1: Классический оверлей</h2>
      <div class="banner banner-1">
        <a href="<?php echo esc_url($permalink); ?>" class="banner-link">
          <div class="banner-image" style="background-image: url('<?php echo esc_url($thumbnail_full); ?>');"></div>
          <div class="banner-overlay"></div>
          <div class="banner-content">
            <h3 class="banner-title"><?php echo esc_html($title); ?></h3>
            <p class="banner-excerpt"><?php echo esc_html($excerpt); ?></p>
            <div class="banner-meta">
              <?php if ($fromnew): ?>
                <span class="meta-price">от <?php echo esc_html($fromnew); ?> €</span>
              <?php endif; ?>
              <?php if ($capacity): ?>
                <span class="meta-capacity"><?php echo esc_html($capacity); ?> гостей</span>
              <?php endif; ?>
            </div>
            <span class="banner-cta">Подробнее →</span>
          </div>
        </a>
      </div>
    </section>

    <!-- Вариант 2: Split-screen (изображение + контент) -->
    <section class="banner-variant">
      <h2>Вариант 2: Split-screen</h2>
      <div class="banner banner-2">
        <a href="<?php echo esc_url($permalink); ?>" class="banner-link">
          <div class="banner-split-image" style="background-image: url('<?php echo esc_url($thumbnail_full); ?>');"></div>
          <div class="banner-split-content">
            <div class="banner-split-inner">
              <span class="banner-label">Свадебная локация</span>
              <h3 class="banner-title"><?php echo esc_html($title); ?></h3>
              <p class="banner-excerpt"><?php echo esc_html($excerpt); ?></p>
              <div class="banner-features">
                <?php if ($fromnew): ?>
                  <div class="feature-item">
                    <span class="feature-icon">💰</span>
                    <span>от <?php echo esc_html($fromnew); ?> €</span>
                  </div>
                <?php endif; ?>
                <?php if ($capacity): ?>
                  <div class="feature-item">
                    <span class="feature-icon">👥</span>
                    <span><?php echo esc_html($capacity); ?> гостей</span>
                  </div>
                <?php endif; ?>
                <?php if ($cer_time): ?>
                  <div class="feature-item">
                    <span class="feature-icon">⏰</span>
                    <span><?php echo esc_html($cer_time); ?></span>
                  </div>
                <?php endif; ?>
              </div>
              <button class="banner-button">Узнать больше</button>
            </div>
          </div>
        </a>
      </div>
    </section>

    <!-- Вариант 3: Карточка с ховер-эффектом -->
    <section class="banner-variant">
      <h2>Вариант 3: Интерактивная карточка</h2>
      <div class="banner banner-3">
        <a href="<?php echo esc_url($permalink); ?>" class="banner-card">
          <div class="card-image-wrap">
            <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($title); ?>" class="card-image">
            <div class="card-badge">Премиум</div>
          </div>
          <div class="card-body">
            <h3 class="card-title"><?php echo esc_html($title); ?></h3>
            <p class="card-description"><?php echo esc_html($excerpt); ?></p>
            <div class="card-footer">
              <div class="card-price">
                <?php if ($fromnew): ?>
                  <span class="price-label">от</span>
                  <span class="price-value"><?php echo esc_html($fromnew); ?> €</span>
                <?php endif; ?>
              </div>
              <span class="card-arrow">→</span>
            </div>
          </div>
        </a>
      </div>
    </section>

    <!-- Вариант 4: Минималистичный с фокусом на изображение -->
    <section class="banner-variant">
      <h2>Вариант 4: Минималистичный</h2>
      <div class="banner banner-4">
        <a href="<?php echo esc_url($permalink); ?>" class="minimal-banner">
          <div class="minimal-image-container">
            <img src="<?php echo esc_url($thumbnail_full); ?>" alt="<?php echo esc_attr($title); ?>" class="minimal-image">
            <div class="minimal-overlay">
              <div class="minimal-content">
                <span class="minimal-label">Свадьба в</span>
                <h3 class="minimal-title"><?php echo esc_html($title); ?></h3>
                <?php if ($fromnew): ?>
                  <span class="minimal-price">от <?php echo esc_html($fromnew); ?> €</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </a>
      </div>
    </section>

    <!-- Вариант 5: Магазинный стиль (e-commerce) -->
    <section class="banner-variant">
      <h2>Вариант 5: Магазинный стиль</h2>
      <div class="banner banner-5">
        <a href="<?php echo esc_url($permalink); ?>" class="shop-card">
          <div class="shop-image-wrap">
            <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($title); ?>" class="shop-image">
            <div class="shop-quick-view">Быстрый просмотр</div>
          </div>
          <div class="shop-info">
            <div class="shop-category">Свадебная локация</div>
            <h3 class="shop-title"><?php echo esc_html($title); ?></h3>
            <div class="shop-rating">
              <span class="stars">★★★★★</span>
              <span class="reviews">(48 отзывов)</span>
            </div>
            <div class="shop-details">
              <div class="shop-price">
                <?php if ($fromnew): ?>
                  <span class="price-from">от</span>
                  <span class="price-amount"><?php echo esc_html($fromnew); ?> €</span>
                <?php endif; ?>
              </div>
              <button class="shop-btn">Выбрать</button>
            </div>
          </div>
        </a>
      </div>
    </section>

    <!-- Вариант 6: Кинематографичный (широкий баннер) -->
    <section class="banner-variant">
      <h2>Вариант 6: Кинематографичный</h2>
      <div class="banner banner-6">
        <a href="<?php echo esc_url($permalink); ?>" class="cinematic-banner">
          <div class="cinematic-bg" style="background-image: url('<?php echo esc_url($thumbnail_full); ?>');"></div>
          <div class="cinematic-content-wrap">
            <div class="cinematic-content">
              <span class="cinematic-category">Премиум локация</span>
              <h3 class="cinematic-title"><?php echo esc_html($title); ?></h3>
              <p class="cinematic-description"><?php echo esc_html($excerpt); ?></p>
              <div class="cinematic-stats">
                <?php if ($capacity): ?>
                  <div class="stat">
                    <span class="stat-value"><?php echo esc_html($capacity); ?></span>
                    <span class="stat-label">гостей</span>
                  </div>
                <?php endif; ?>
                <?php if ($fromnew): ?>
                  <div class="stat">
                    <span class="stat-value"><?php echo esc_html($fromnew); ?> €</span>
                    <span class="stat-label">от</span>
                  </div>
                <?php endif; ?>
                <?php if ($cer_time): ?>
                  <div class="stat">
                    <span class="stat-value"><?php echo esc_html($cer_time); ?></span>
                    <span class="stat-label">церемония</span>
                  </div>
                <?php endif; ?>
              </div>
              <button class="cinematic-btn">
                <span>Забронировать</span>
                <span class="btn-arrow">→</span>
              </button>
            </div>
          </div>
        </a>
      </div>
    </section>

  </div>
</main>

<?php get_footer(); ?>
