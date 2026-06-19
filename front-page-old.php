<?php
/**
 * Template Name: Front Page
 * Description: Главная страница сайта Beautiful Wedding
 */

if (!defined('ABSPATH')) {
  exit;
}

if (!function_exists('bw_enqueue_front_page_assets')) {
  function bw_enqueue_front_page_assets()
  {
    $css_file = get_template_directory() . '/assets/css/front-page.css';
    $css_version = file_exists($css_file) ? filemtime($css_file) : wp_get_theme()->get('Version');
    wp_enqueue_style('front-page-styles', get_template_directory_uri() . '/assets/css/front-page.css', array('minimal-style'), $css_version);
  }
}
add_action('wp_enqueue_scripts', 'bw_enqueue_front_page_assets');

$scenario_configs = array(
  'svadba-v-prage' => array(
    'title' => 'Свадьба в Праге',
    'lead' => 'Город романтики, архитектуры и атмосферных площадок для церемонии.',
    'description' => 'Исторические дворцы, панорамы Влтавы, старинные залы и уютные сады создают идеальную сцену для вашего дня. Мы бережно собираем маршрут и тайминг так, чтобы вы наслаждались моментом, а не организационными деталями.',
    'points' => array('Иконичные городские виды', 'Фотогеничные маршруты в центре', 'Гибкие сценарии для разного формата'),
    'term_url' => '/location/svadba-v-prage/',
  ),
  'svadba-v-zamke-chehii' => array(
    'title' => 'Свадьба в замке',
    'lead' => 'Торжественная классика в исторических интерьерах Чехии.',
    'description' => 'Замковые комплексы с парками и парадными залами подходят для камерной церемонии и для большого праздника. Мы продумываем логистику, стилистику и сопровождение, чтобы все выглядело безупречно.',
    'points' => array('Величественные интерьеры', 'Парадные локации для фото', 'Полный координационный контроль'),
    'term_url' => '/location/svadba-v-zamke-chehii/',
  ),
  'svadba-na-korable' => array(
    'title' => 'Свадьба на корабле',
    'lead' => 'Легкая динамика воды, закатный свет и отдельный мир для вас и гостей.',
    'description' => 'Церемония на борту или прогулка после регистрации становится ярким акцентом свадебного дня. Мы подбираем лучший формат маршрута, тайминг выхода и стилистику под вашу историю.',
    'points' => array('Необычный формат праздника', 'Панорамные кадры на воде', 'Комфортный сценарий для гостей'),
    'term_url' => '/location/svadba-na-korable/',
  ),
);

$scenarios = array();

// Карта slug => объект термина.
// Нужна, чтобы позже собрать итоговые данные сценария (название, ссылка на термин и т.д.)
// без повторных запросов get_term_by().
$scenario_term_by_slug = array();

// Карта term_taxonomy_id => slug.
// В SQL мы получаем term_taxonomy_id, а сценарии у нас привязаны к slug,
// поэтому этот массив связывает результат SQL с нужным блоком сценария.
$scenario_slug_by_tt_id = array();

foreach (array_keys($scenario_configs) as $slug) {
  $term = get_term_by('slug', $slug, 'location');
  if ($term && !is_wp_error($term)) {
    $scenario_term_by_slug[$slug] = $term;
    $scenario_slug_by_tt_id[(int) $term->term_taxonomy_id] = $slug;
  }
}

$scenario_selected_ids = array();
// Хранилище выбранных постов по схеме [slug][menu_order] = post_id.
// Позволяет взять ровно по одному посту для order 1,2,3 и 7 на каждую локацию.
if (!empty($scenario_slug_by_tt_id)) {
  global $wpdb;

  $sql = "SELECT tr.term_taxonomy_id, p.ID, p.menu_order
     FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
     INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
     WHERE p.post_type = 'svadba'
       AND p.post_status = 'publish'
       AND tt.taxonomy = 'location'
       AND p.menu_order IN (1, 2, 3, 7)
     ORDER BY tr.term_taxonomy_id ASC, p.menu_order ASC, p.post_date DESC";

  $rows = $wpdb->get_results($sql);
  if (is_array($rows)) {
    foreach ($rows as $row) {
      $tt_id = (int) $row->term_taxonomy_id;
      $post_id = (int) $row->ID;
      $menu_order = (int) $row->menu_order;

      if (!isset($scenario_slug_by_tt_id[$tt_id])) {
        continue;
      }

      $slug = $scenario_slug_by_tt_id[$tt_id];
      if (!isset($scenario_selected_ids[$slug])) {
        $scenario_selected_ids[$slug] = array();
      }

      // Keep one post per required menu_order, prioritizing newest by date.
      if (!isset($scenario_selected_ids[$slug][$menu_order])) {
        $scenario_selected_ids[$slug][$menu_order] = $post_id;
      }
    }
  }
}

foreach ($scenario_configs as $slug => $config) {
  if (!isset($scenario_term_by_slug[$slug])) {
    continue;
  }

  $term = $scenario_term_by_slug[$slug];
  $picked_ids = isset($scenario_selected_ids[$slug]) ? $scenario_selected_ids[$slug] : array();

  $popular_ids = array();
  foreach (array(1, 2, 3) as $popular_order) {
    if (isset($picked_ids[$popular_order])) {
      $popular_ids[] = (int) $picked_ids[$popular_order];
    }
  }

  $best_offer_id = isset($picked_ids[7]) ? (int) $picked_ids[7] : 0;
  if ($best_offer_id === 0 && !empty($popular_ids)) {
    $best_offer_id = (int) $popular_ids[0];
  }

  $hero_image_id = $best_offer_id > 0 ? (int) get_post_thumbnail_id($best_offer_id) : 0;
  if ($hero_image_id === 0 && !empty($popular_ids)) {
    $hero_image_id = (int) get_post_thumbnail_id((int) $popular_ids[0]);
  }

  $scenarios[] = array(
    'slug'         => $slug,
    'term_name'    => $term->name,
    'term_link'    => get_term_link($term),
    'title'        => $config['title'],
    'lead'         => $config['lead'],
    'description'  => $config['description'],
    'points'       => $config['points'],
    'best_offer'   => $best_offer_id,
    'popular'      => $popular_ids,
    'hero_image'   => $hero_image_id,
    'fallback_url' => home_url($config['term_url']),
  );
}

$services_ids = get_posts(array(
  'post_type'              => 'service',
  'post_status'            => 'publish',
  'posts_per_page'         => 6,
  'fields'                 => 'ids',
  'orderby'                => array(
    'menu_order' => 'ASC',
    'date'       => 'DESC',
  ),
  'no_found_rows'          => true,
  'update_post_meta_cache' => false,
  'update_post_term_cache' => false,
));

$gallery_ids = get_posts(array(
  'post_type'              => 'svadba',
  'post_status'            => 'publish',
  'posts_per_page'         => 8,
  'fields'                 => 'ids',
  'orderby'                => 'date',
  'order'                  => 'DESC',
  'no_found_rows'          => true,
  'update_post_meta_cache' => false,
  'update_post_term_cache' => false,
));

get_header('front-page');
?>

<main class="bw-front-page overflowed">
  <section class="bw-home-hero full" id="top">
    <div class="bw-home-hero__media" aria-hidden="true"></div>
    <div class="bw-home-hero__overlay" aria-hidden="true"></div>
    <div class="bw-home-hero__content boxed">
      <p class="bw-home-kicker">Wedding Best</p>
      <h1 class="bw-home-title">Свадьба в Чехии<br>как кинематографичная история</h1>
      <p class="bw-home-lead">Создаем атмосферные церемонии в Праге, замках и на воде. Бережно ведем вас от первой консультации до финального бокала в день свадьбы.</p>
      <div class="bw-home-hero__actions">
        <a class="bw-btn bw-btn--main" href="#scenarios">Выбрать локацию</a>
        <a class="bw-btn bw-btn--ghost" href="#contact">Обсудить ваш день</a>
      </div>
    </div>
  </section>

  <section class="bw-home-trust full" aria-label="Преимущества">
    <div class="bw-home-trust__inner boxed">
      <div class="bw-home-metric"><strong>10+ лет</strong><span>опыта в Чехии</span></div>
      <div class="bw-home-metric"><strong>350+</strong><span>проведенных свадеб</span></div>
      <div class="bw-home-metric"><strong>3 языка</strong><span>сопровождения пары</span></div>
      <div class="bw-home-metric"><strong>Полный цикл</strong><span>организации и координации</span></div>
    </div>
  </section>

  <section class="bw-home-services boxed" id="services">
    <div class="bw-section-head">
      <p class="bw-home-kicker">Сервис</p>
      <h2>Берем на себя всю организацию</h2>
      <p>От концепции и логистики до командной координации в день церемонии. Вы фокусируетесь на эмоциях, мы закрываем процессы.</p>
    </div>
    <div class="bw-service-grid">
      <?php if (!empty($services_ids)) : ?>
        <?php foreach ($services_ids as $service_id) : ?>
          <article class="bw-service-card">
            <a class="bw-service-card__image" href="<?php echo esc_url(get_permalink($service_id)); ?>">
              <?php if (has_post_thumbnail($service_id)) : ?>
                <?php echo get_the_post_thumbnail($service_id, 'large', array('loading' => 'lazy')); ?>
              <?php endif; ?>
            </a>
            <div class="bw-service-card__body">
              <h3><a href="<?php echo esc_url(get_permalink($service_id)); ?>"><?php echo esc_html(get_the_title($service_id)); ?></a></h3>
              <p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_post_field('post_excerpt', $service_id) ?: get_post_field('post_content', $service_id)), 20)); ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else : ?>
        <p class="bw-home-fallback">Список услуг заполняется. Мы уже готовим этот блок.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="bw-home-scenarios" id="scenarios">
    <?php foreach ($scenarios as $index => $scenario) : ?>
      <?php
      $term_link = is_string($scenario['term_link']) ? $scenario['term_link'] : $scenario['fallback_url'];
      $is_reverse = $index % 2 !== 0;
      $style_attr = '';
      if ((int) $scenario['hero_image'] > 0) {
        $background_url = wp_get_attachment_image_url((int) $scenario['hero_image'], 'full');
        if (is_string($background_url) && $background_url !== '') {
          $style_attr = ' style="--scenario-bg:url(\'' . esc_url($background_url) . '\');"';
        }
      }
      ?>
      <section class="bw-scenario full<?php echo $is_reverse ? ' bw-scenario--reverse' : ''; ?>"<?php echo $style_attr; ?>>
        <div class="bw-scenario__parallax" aria-hidden="true"></div>
        <div class="bw-scenario__overlay" aria-hidden="true"></div>
        <div class="bw-scenario__inner boxed">
          <div class="bw-scenario__content">
            <p class="bw-home-kicker"><?php echo esc_html($scenario['term_name']); ?></p>
            <h2><?php echo esc_html($scenario['title']); ?></h2>
            <p class="bw-scenario__lead"><?php echo esc_html($scenario['lead']); ?></p>
            <p><?php echo esc_html($scenario['description']); ?></p>
            <ul>
              <?php foreach ($scenario['points'] as $point) : ?>
                <li><?php echo esc_html($point); ?></li>
              <?php endforeach; ?>
            </ul>
            <a class="bw-btn bw-btn--main" href="<?php echo esc_url($term_link); ?>">Смотреть локации</a>
          </div>

          <div class="bw-scenario__cards">
            <?php if ((int) $scenario['best_offer'] > 0) : ?>
              <article class="bw-scenario-best">
                <a class="bw-scenario-best__image" href="<?php echo esc_url(get_permalink((int) $scenario['best_offer'])); ?>">
                  <?php if (has_post_thumbnail((int) $scenario['best_offer'])) : ?>
                    <?php echo get_the_post_thumbnail((int) $scenario['best_offer'], 'large', array('loading' => 'lazy')); ?>
                  <?php endif; ?>
                  <span>Лучшее предложение</span>
                </a>
                <h3><a href="<?php echo esc_url(get_permalink((int) $scenario['best_offer'])); ?>"><?php echo esc_html(get_the_title((int) $scenario['best_offer'])); ?></a></h3>
                <p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_post_field('post_excerpt', (int) $scenario['best_offer']) ?: get_post_field('post_content', (int) $scenario['best_offer'])), 22)); ?></p>
              </article>
            <?php endif; ?>

            <?php if (!empty($scenario['popular'])) : ?>
              <div class="bw-scenario-popular">
                <?php foreach ($scenario['popular'] as $popular_id) : ?>
                  <a class="bw-scenario-popular__item" href="<?php echo esc_url(get_permalink((int) $popular_id)); ?>">
                    <?php if (has_post_thumbnail((int) $popular_id)) : ?>
                      <?php echo get_the_post_thumbnail((int) $popular_id, 'medium_large', array('loading' => 'lazy')); ?>
                    <?php endif; ?>
                    <span><?php echo esc_html(get_the_title((int) $popular_id)); ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>
    <?php endforeach; ?>
  </section>

  <section class="bw-home-process boxed">
    <div class="bw-section-head">
      <p class="bw-home-kicker">Путь пары</p>
      <h2>Как мы работаем</h2>
    </div>
    <ol class="bw-process-line" aria-label="Этапы работы">
      <li><strong>1. Знакомство</strong><span>Уточняем формат свадьбы, стиль и ваши ожидания.</span></li>
      <li><strong>2. Концепция</strong><span>Предлагаем площадки, сценарий дня и команду специалистов.</span></li>
      <li><strong>3. Подготовка</strong><span>Собираем документы, бронируем локации и согласуем тайминг.</span></li>
      <li><strong>4. Координация</strong><span>Ведем ваш день, контролируя все процессы на площадке.</span></li>
      <li><strong>5. Финал</strong><span>Остаются только впечатления, фото и ваша личная история.</span></li>
    </ol>
  </section>

  <section class="bw-home-gallery full">
    <div class="bw-home-gallery__intro boxed">
      <p class="bw-home-kicker">Галерея</p>
      <h2>Живые кадры наших свадеб</h2>
    </div>
    <div class="bw-home-gallery__grid boxed">
      <?php if (!empty($gallery_ids)) : ?>
        <?php foreach ($gallery_ids as $gallery_id) : ?>
          <a class="bw-gallery-item" href="<?php echo esc_url(get_permalink((int) $gallery_id)); ?>">
            <?php if (has_post_thumbnail((int) $gallery_id)) : ?>
              <?php echo get_the_post_thumbnail((int) $gallery_id, 'large', array('loading' => 'lazy')); ?>
            <?php endif; ?>
            <span><?php echo esc_html(get_the_title((int) $gallery_id)); ?></span>
          </a>
        <?php endforeach; ?>
      <?php else : ?>
        <p class="bw-home-fallback">Галерея пополняется новыми историями.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="bw-home-contact full" id="contact">
    <div class="bw-home-contact__overlay" aria-hidden="true"></div>
    <div class="bw-home-contact__inner boxed">
      <p class="bw-home-kicker">Начнем подготовку</p>
      <h2>Расскажите о вашей свадьбе,<br>и мы предложим лучший сценарий</h2>
      <p>Заполните анкету или напишите нам в мессенджер. Первая консультация проходит в удобном для вас формате.</p>
      <div class="bw-home-contact__actions">
        <a class="bw-btn bw-btn--main" href="/anketa/">Оставить заявку</a>
        <a class="bw-btn bw-btn--ghost" href="/kontakty/">Открыть контакты</a>
      </div>
    </div>
  </section>
</main>

<script>
  (function() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }

    var layers = document.querySelectorAll('.bw-scenario__parallax');
    if (!layers.length) {
      return;
    }

    var ticking = false;

    function isDesktop() {
      return window.matchMedia('(min-width: 861px)').matches;
    }

    function updateParallax() {
      if (!isDesktop()) {
        layers.forEach(function(layer) {
          layer.style.transform = 'none';
        });
        return;
      }

      var rootStyles = window.getComputedStyle(document.documentElement);
      var intensity = parseFloat(rootStyles.getPropertyValue('--parallax-intensity')) || 1;
      var scenarioSpeedBase = parseFloat(rootStyles.getPropertyValue('--parallax-speed-scenario')) || 0.10;
      var parallaxScale = parseFloat(rootStyles.getPropertyValue('--parallax-scale')) || 1.08;
      var viewportCenter = window.innerHeight / 2;

      layers.forEach(function(layer) {
        var host = layer.parentElement;
        if (!host) {
          return;
        }

        var rect = host.getBoundingClientRect();
        var sectionCenter = rect.top + (rect.height / 2);
        var distanceFromCenter = sectionCenter - viewportCenter;
        var speed = scenarioSpeedBase * intensity;
        var offsetY = -distanceFromCenter * speed;

        layer.style.transform = 'translate3d(0, ' + offsetY.toFixed(2) + 'px, 0) scale(' + parallaxScale + ')';
      });
    }

    function requestTick() {
      if (ticking) {
        return;
      }

      ticking = true;
      window.requestAnimationFrame(function() {
        updateParallax();
        ticking = false;
      });
    }

    window.addEventListener('scroll', requestTick, { passive: true });
    window.addEventListener('resize', requestTick);
    requestTick();
  })();
</script>

<?php get_footer(); ?>
