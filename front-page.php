<?php
/**
 * Template Name: Front Page
 * Description: Главная страница сайта Beautiful Wedding
 */

// Подключаем стили для главной страницы с версифицированием
function enqueue_front_page_assets() {
    $css_file = get_template_directory() . '/assets/css/front-page.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : wp_get_theme()->get('Version');
    wp_enqueue_style('front-page-styles', get_template_directory_uri() . '/assets/css/front-page.css', array('minimal-style'), $css_version);
}
add_action('wp_enqueue_scripts', 'enqueue_front_page_assets');

// Используем отдельный заголовок для главной страницы
include get_template_directory() . '/header-front-page.php';
?>

<main class="front-page">
  
  <!-- Hero Section -->
  <section class="hero-section full">
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1 class="hero-title">Beautiful Wedding</h1>
      <p class="hero-subtitle">Организация свадеб в Чехии</p>
      <p class="hero-description">Создадим для вас незабываемую свадьбу в самом сердце Европы</p>
      <div class="hero-cta">
        <a href="#services" class="btn btn-primary">Наши услуги</a>
        <a href="#contact" class="btn btn-secondary">Связаться с нами</a>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section class="about-section boxed">
    <div class="section-content">
      <h2>О нас</h2>
      <div class="about-text">
        <p>Мы специализируемся на организации свадебных церемоний в Чехии. Наша команда профессионалов поможет вам создать идеальный день, о котором вы всегда мечтали.</p>
        <p>С нами ваша свадьба в Праге станет незабываемым событием, наполненным романтикой и волшебством.</p>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section id="services" class="services-section boxed">
    <h2>Наши услуги</h2>
    <div class="services-grid">
      
      <?php
      // Получаем записи типа "service"
      $services_query = new WP_Query(array(
        'post_type' => 'service',
        'posts_per_page' => 6,
        'orderby' => 'menu_order',
        'order' => 'ASC'
      ));

      if ($services_query->have_posts()) :
        while ($services_query->have_posts()) : $services_query->the_post();
      ?>
          <div class="service-card">
            <?php if (has_post_thumbnail()) : ?>
              <div class="service-image">
                <a href="<?php the_permalink(); ?>">
                  <?php the_post_thumbnail('medium'); ?>
                </a>
              </div>
            <?php endif; ?>
            <div class="service-content">
              <h3 class="service-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
              </h3>
              <div class="service-excerpt">
                <?php the_excerpt(); ?>
              </div>
              <a href="<?php the_permalink(); ?>" class="service-link">Подробнее →</a>
            </div>
          </div>
      <?php
        endwhile;
        wp_reset_postdata();
      else :
      ?>
        <p>Услуги скоро будут добавлены.</p>
      <?php endif; ?>
      
    </div>
    <div class="services-cta">
      <a href="<?php echo get_post_type_archive_link('service'); ?>" class="btn btn-primary">Все услуги</a>
    </div>
  </section>

  <!-- Weddings Section -->
  <section class="weddings-section boxed">
    <h2>Наши свадьбы</h2>
    <div class="weddings-grid">
      
      <?php
      // Получаем записи типа "svadba"
      $weddings_query = new WP_Query(array(
        'post_type' => 'svadba',
        'posts_per_page' => 3,
        'orderby' => 'date',
        'order' => 'DESC'
      ));

      if ($weddings_query->have_posts()) :
        while ($weddings_query->have_posts()) : $weddings_query->the_post();
      ?>
          <div class="wedding-card">
            <?php if (has_post_thumbnail()) : ?>
              <div class="wedding-image">
                <a href="<?php the_permalink(); ?>">
                  <?php the_post_thumbnail('large'); ?>
                </a>
              </div>
            <?php endif; ?>
            <div class="wedding-content">
              <h3 class="wedding-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
              </h3>
              <div class="wedding-date">
                <?php echo get_the_date(); ?>
              </div>
            </div>
          </div>
      <?php
        endwhile;
        wp_reset_postdata();
      else :
      ?>
        <p>Скоро здесь появятся фотографии наших свадеб.</p>
      <?php endif; ?>
      
    </div>
    <div class="weddings-cta">
      <a href="<?php echo get_post_type_archive_link('svadba'); ?>" class="btn btn-primary">Посмотреть все свадьбы</a>
    </div>
  </section>

  <!-- Why Us Section -->
  <section class="why-us-section boxed">
    <h2>Почему выбирают нас</h2>
    <div class="features-grid">
      <div class="feature-item">
        <div class="feature-icon">✓</div>
        <h3>Опыт</h3>
        <p>Более 10 лет организуем свадьбы в Чехии</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">♥</div>
        <h3>Индивидуальный подход</h3>
        <p>Учитываем все ваши пожелания и предпочтения</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">★</div>
        <h3>Профессионализм</h3>
        <p>Работаем с лучшими специалистами</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">☆</div>
        <h3>Комплексный сервис</h3>
        <p>Организуем все от документов до банкета</p>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="contact-section boxed">
    <h2>Свяжитесь с нами</h2>
    <div class="contact-content">
      <p>Готовы начать планирование вашей свадьбы мечты? Мы всегда рады ответить на ваши вопросы!</p>
      <div class="contact-info">
        <p><strong>Email:</strong> <a href="mailto:info@beautifulwedding.cz">info@beautifulwedding.cz</a></p>
        <p><strong>Телефон:</strong> <a href="tel:+420123456789">+420 123 456 789</a></p>
      </div>
      <div class="contact-cta">
        <a href="/anketa" class="btn btn-primary btn-large">Заполнить анкету</a>
      </div>
    </div>
  </section>

</main>

<?php get_footer(); ?>
