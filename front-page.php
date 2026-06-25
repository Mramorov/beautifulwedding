<?php
/**
 * Template Name: Front Page
 * Description: Главная страница сайта Beautiful Wedding
 */

if (!defined('ABSPATH')) {
  exit;
}

/* -----------------------------------------------
   Scenario configs
----------------------------------------------- */
$scenario_configs = array(
  'svadba-v-prage' => array(
    'title'          => 'Свадьба в Праге',
    'lead'           => 'Город романтики, архитектуры и атмосферных площадок.',
    'description'    => 'Исторические дворцы, панорамы Влтавы, старинные залы и уютные сады создают идеальную сцену для вашего дня. Мы бережно собираем маршрут и тайминг так, чтобы вы наслаждались моментом.',
    'points'         => array('Иконичные городские виды', 'Фотогеничные маршруты в центре', 'Гибкие сценарии для разного формата'),
    'term_url'       => '/location/svadba-v-prage/',
    'popular_orders' => array(1, 2),
  ),
  'svadba-v-zamke-chehii' => array(
    'title'          => 'Свадьба в замке',
    'lead'           => 'Торжественная классика в исторических интерьерах Чехии.',
    'description'    => 'Замковые комплексы с парками и парадными залами подходят для камерной церемонии и для большого праздника. Мы продумываем логистику, стилистику и сопровождение.',
    'points'         => array('Величественные интерьеры', 'Парадные локации для фото', 'Полный координационный контроль'),
    'term_url'       => '/location/svadba-v-zamke-chehii/',
    'popular_orders' => array(3, 4),
  ),
  'svadba-na-korable' => array(
    'title'          => 'Свадьба на корабле',
    'lead'           => 'Закатный свет, вода и отдельный мир для вас и гостей.',
    'description'    => 'Церемония на борту или прогулка после регистрации становится ярким акцентом свадебного дня. Мы подбираем лучший формат маршрута, тайминг выхода и стилистику.',
    'points'         => array('Необычный формат праздника', 'Панорамные кадры на воде', 'Комфортный сценарий для гостей'),
    'term_url'       => '/location/svadba-na-korable/',
    'popular_orders' => array(1, 2),
  ),
);

/* -----------------------------------------------
   Build scenario data with images and popular posts
----------------------------------------------- */
$scenarios              = array();
$scenario_term_by_slug  = array();
$scenario_slug_by_tt_id = array();

foreach (array_keys($scenario_configs) as $slug) {
  $term = get_term_by('slug', $slug, 'location');
  if ($term && !is_wp_error($term)) {
    $scenario_term_by_slug[$slug]                          = $term;
    $scenario_slug_by_tt_id[(int) $term->term_taxonomy_id] = $slug;
  }
}

$scenario_selected_ids = array();
if (!empty($scenario_slug_by_tt_id)) {
  global $wpdb;
  $sql = "SELECT tr.term_taxonomy_id, p.ID, p.menu_order
     FROM {$wpdb->posts} p
     INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
     INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
     WHERE p.post_type = 'svadba'
       AND p.post_status = 'publish'
       AND tt.taxonomy = 'location'
       AND p.menu_order <= 7
     ORDER BY tr.term_taxonomy_id ASC, p.menu_order ASC, p.post_date DESC";

  $rows = $wpdb->get_results($sql);
  if (is_array($rows)) {
    foreach ($rows as $row) {
      $tt_id      = (int) $row->term_taxonomy_id;
      $post_id    = (int) $row->ID;
      $menu_order = (int) $row->menu_order;
      if (!isset($scenario_slug_by_tt_id[$tt_id])) {
        continue;
      }
      $slug = $scenario_slug_by_tt_id[$tt_id];
      if (!isset($scenario_selected_ids[$slug])) {
        $scenario_selected_ids[$slug] = array();
      }
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
  $term       = $scenario_term_by_slug[$slug];
  $picked_ids = isset($scenario_selected_ids[$slug]) ? $scenario_selected_ids[$slug] : array();

  $popular_ids = array();
  foreach ($config['popular_orders'] as $ord) {
    if (isset($picked_ids[$ord])) {
      $popular_ids[] = (int) $picked_ids[$ord];
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
    'slug'        => $slug,
    'term_name'   => $term->name,
    'term_link'   => get_term_link($term),
    'title'       => $config['title'],
    'lead'        => $config['lead'],
    'description' => $config['description'],
    'points'      => $config['points'],
    'best_offer'  => $best_offer_id,
    'popular'     => $popular_ids,
    'hero_image'  => $hero_image_id,
    'fallback_url'=> home_url($config['term_url']),
  );
}

/* -----------------------------------------------
   Services + gallery posts
----------------------------------------------- */
$services_ids = get_posts(array(
  'post_type'              => 'service',
  'post_status'            => 'publish',
  'posts_per_page'         => 6,
  'fields'                 => 'ids',
  'orderby'                => array('menu_order' => 'ASC', 'date' => 'DESC'),
  'no_found_rows'          => true,
  'update_post_meta_cache' => false,
  'update_post_term_cache' => false,
));

$gallery_menu_order_filter = function( $where ) {
  global $wpdb;
  $where .= " AND {$wpdb->posts}.menu_order > 7";
  return $where;
};
add_filter( 'posts_where', $gallery_menu_order_filter );

$gallery_ids = get_posts(array(
  'post_type'              => 'svadba',
  'post_status'            => 'publish',
  'posts_per_page'         => 12,
  'fields'                 => 'ids',
  'orderby'                => 'date',
  'order'                  => 'DESC',
  'no_found_rows'          => true,
  'update_post_meta_cache' => false,
  'update_post_term_cache' => false,
));

remove_filter( 'posts_where', $gallery_menu_order_filter );

$fp_gallery_ids = array_filter((array) get_post_meta(2436, 'svadba_gallery', true));

/* -----------------------------------------------
   Hero background — page featured image or fallback
----------------------------------------------- */
$fp_post_id  = get_queried_object_id();
$fp_hero_url = $fp_post_id ? (string) get_the_post_thumbnail_url($fp_post_id, 'full') : '';
if ($fp_hero_url === '') {
  $fp_hero_url = get_template_directory_uri() . '/img/photo-10.png';
}

set_query_var('bw_fp_custom_hero', true);
get_header('front-page');
?>

<main class="bw-front-page layout overflowed">

  <!-- ===================== HERO ===================== -->
  <section class="bw-fp-hero svadba-hero full" id="top"
    style="background-image: url('<?php echo esc_url($fp_hero_url); ?>');">
    <div class="bw-fp-hero__overlay" aria-hidden="true"></div>
    <div class="svadba-hero-content bw-fp-hero__content">
      <p class="bw-fp-kicker" style="color: rgba(220,185,155,0.9)">Wedding Best</p>
      <?php
      set_query_var('animated_title_text', 'Свадьба в Чехии');
      get_template_part('templates/animated-title');
      ?>
      <p class="bw-fp-subtitle">Создаём атмосферные церемонии в Праге, замках и на воде — от первой консультации до финального бокала шампанского в день свадьбы.</p>
      <div class="bw-fp-hero__actions">
        <a href="#scenarios" class="bw-fp-btn bw-fp-btn--main">Выбрать локацию</a>
        <a href="/anketa/" class="bw-fp-btn bw-fp-btn--ghost">Обсудить ваш день</a>
      </div>
    </div>
    <?php get_template_part('templates/wave-clip'); ?>
  </section>

  <!-- ===================== TRUST BAR ===================== -->
  <section class="bw-fp-trust full" aria-label="Наши достижения">
    <span class="bw-fp-watermark" aria-hidden="true">Legacy</span>
    <div class="bw-fp-trust__inner boxed">
      <div class="bw-fp-metric">
        <strong>10+ лет</strong>
        <span>опыта в Чехии</span>
      </div>
      <div class="bw-fp-metric">
        <strong>350+</strong>
        <span>проведённых свадеб</span>
      </div>
      <div class="bw-fp-metric">
        <strong>3 языка</strong>
        <span>сопровождения пары</span>
      </div>
      <div class="bw-fp-metric">
        <strong>Полный цикл</strong>
        <span>организации и координации</span>
      </div>
    </div>
  </section>

  <!-- ===================== SCENARIOS ===================== -->
  <div class="bw-fp-scenarios full" id="scenarios">
    <?php $scenario_count = count($scenarios); ?>
    <?php foreach ($scenarios as $index => $scenario) : ?>
      <?php
      $term_link  = is_string($scenario['term_link']) ? $scenario['term_link'] : $scenario['fallback_url'];
      $is_reverse = $index % 2 !== 0;
      $bg_url     = '';
      if ((int) $scenario['hero_image'] > 0) {
        $bg_url = (string) wp_get_attachment_image_url((int) $scenario['hero_image'], 'full');
      }
      ?>
      <section class="bw-fp-scenario full<?php echo $is_reverse ? ' bw-fp-scenario--reverse' : ''; ?><?php echo !$is_reverse ? ' bw-fp-scenario--light' : ''; ?>"<?php echo ($bg_url !== '') ? ' style="--scenario-bg:url(\'' . esc_url($bg_url) . '\');"' : ''; ?>>
        <div class="bw-fp-scenario__bg" aria-hidden="true"></div>
        <div class="bw-fp-scenario__inner boxed">

          <div class="bw-fp-scenario__text">
            <p class="bw-fp-kicker"><?php echo esc_html($scenario['term_name']); ?></p>
            <h2><?php echo esc_html($scenario['title']); ?></h2>
            <p class="bw-fp-scenario__lead"><?php echo esc_html($scenario['lead']); ?></p>
            <p><?php echo esc_html($scenario['description']); ?></p>
            <ul class="bw-fp-scenario__points">
              <?php foreach ($scenario['points'] as $point) : ?>
                <li><?php echo esc_html($point); ?></li>
              <?php endforeach; ?>
            </ul>
            <a class="bw-fp-btn bw-fp-btn--main" href="<?php echo esc_url($term_link); ?>">Смотреть локации</a>
          </div>

          <div class="bw-fp-scenario__cards">
            <?php if ((int) $scenario['best_offer'] > 0) : ?>
              <article class="location-card bw-fp-scenario__main-card">
                <a class="location-card__banner" href="<?php echo esc_url(get_permalink((int) $scenario['best_offer'])); ?>">
                  <?php if (has_post_thumbnail((int) $scenario['best_offer'])) : ?>
                    <?php echo get_the_post_thumbnail((int) $scenario['best_offer'], 'large', array('loading' => 'lazy')); ?>
                  <?php endif; ?>
                  <span class="location-card__title-wrap">
                    <span class="location-card__title"><?php echo esc_html(get_the_title((int) $scenario['best_offer'])); ?></span>
                  </span>
                </a>
              </article>
            <?php endif; ?>

            <?php
            $mini_ids = array_slice($scenario['popular'], 0, 2);
            if (!empty($mini_ids)) :
            ?>
              <div class="bw-fp-scenario__mini-cards">
                <?php foreach ($mini_ids as $mini_id) : ?>
                  <article class="location-card">
                    <a class="location-card__banner" href="<?php echo esc_url(get_permalink((int) $mini_id)); ?>">
                      <?php if (has_post_thumbnail((int) $mini_id)) : ?>
                        <?php echo get_the_post_thumbnail((int) $mini_id, 'medium_large', array('loading' => 'lazy')); ?>
                      <?php endif; ?>
                      <span class="location-card__title-wrap">
                        <span class="location-card__title"><?php echo esc_html(get_the_title((int) $mini_id)); ?></span>
                      </span>
                    </a>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </section>

      <?php if ($index < $scenario_count - 1) : ?>

        <?php if ($index === 0) : ?>
          <!-- ========= SEPARATOR 1: цитата пары ========= -->
          <div class="bw-fp-sep-testimonial full">
            <div class="bw-fp-sep-testimonial__inner">
              <div class="bw-fp-sep-testimonial__body">
                <span class="bw-fp-sep-testimonial__mark" aria-hidden="true">&ldquo;</span>
                <p class="bw-fp-sep-testimonial__quote">Мы даже не представляли, насколько можно влюбиться в Прагу ещё раз. Команда Beautiful Wedding превратила наши мечты в идеальный день — с каждой деталью, с каждым цветком, с каждой улыбкой наших гостей.</p>
                <p class="bw-fp-sep-testimonial__attr">
                  <strong>Анна и Михаил</strong>&nbsp;&middot; Замок Конопиште&nbsp;&middot; Сентябрь 2024
                </p>
              </div>
              <?php echo wp_get_attachment_image(1035, 'medium', false, [
                'class'   => 'bw-fp-sep-testimonial__photo',
                'loading' => 'lazy',
                'alt'     => 'Счастливая пара в день свадьбы',
              ]); ?>
            </div>
          </div>

        <?php else : ?>
          <!-- ========= SEPARATOR 2: заглушка ========= -->
          <div class="bw-fp-sep-placeholder full" aria-hidden="true"></div>

        <?php endif; ?>

      <?php endif; ?>

    <?php endforeach; ?>
  </div>

  <!-- ===================== SERVICES ===================== -->
  <section class="bw-fp-services boxed shrink-animation" id="services">
    <div class="bw-fp-section-head">
      <span class="bw-fp-watermark" aria-hidden="true">Services</span>
      <p class="bw-fp-kicker">Сервис</p>
      <h2>Берём на себя всю организацию</h2>
      <p>От концепции и логистики до командной координации в день церемонии. Вы фокусируетесь на эмоциях — мы закрываем процессы.</p>
    </div>
    <?php if (!empty($services_ids)) : ?>
      <div class="bw-fp-service-grid">
        <?php foreach ($services_ids as $service_id) : ?>
          <article class="location-card">
            <a class="location-card__banner" href="<?php echo esc_url(get_permalink($service_id)); ?>">
              <?php if (has_post_thumbnail($service_id)) : ?>
                <?php echo get_the_post_thumbnail($service_id, 'large', array('loading' => 'lazy')); ?>
              <?php endif; ?>
              <span class="location-card__title-wrap">
                <span class="location-card__title"><?php echo esc_html(get_the_title($service_id)); ?></span>
              </span>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- ===================== PROCESS ===================== -->
  <section class="bw-fp-process full grow-animation">
    <div class="bw-fp-process__inner boxed">
      <div class="bw-fp-section-head">
        <span class="bw-fp-watermark" aria-hidden="true">Journey</span>
        <p class="bw-fp-kicker">Путь пары</p>
        <h2>Как мы работаем</h2>
      </div>
      <ol class="bw-fp-steps" aria-label="Этапы работы">
        <li>
          <span class="bw-fp-step-num">01</span>
          <strong>Знакомство</strong>
          <span>Уточняем формат свадьбы, стиль и ваши ожидания на первой консультации.</span>
        </li>
        <li>
          <span class="bw-fp-step-num">02</span>
          <strong>Концепция</strong>
          <span>Предлагаем площадки, сценарий дня и команду профильных специалистов.</span>
        </li>
        <li>
          <span class="bw-fp-step-num">03</span>
          <strong>Подготовка</strong>
          <span>Собираем документы, бронируем локации и согласуем финальный тайминг.</span>
        </li>
        <li>
          <span class="bw-fp-step-num">04</span>
          <strong>Координация</strong>
          <span>Ведём ваш день, контролируя все процессы на площадке в режиме реального времени.</span>
        </li>
        <li>
          <span class="bw-fp-step-num">05</span>
          <strong>Финал</strong>
          <span>Остаются только впечатления, фотографии и ваша личная история навсегда.</span>
        </li>
      </ol>
    </div>
  </section>

  <!-- ===================== OTHER PLACES ===================== -->
  <section class="bw-fp-other-places full">
    <div class="bw-fp-section-head boxed">
      <span class="bw-fp-watermark" aria-hidden="true">Places</span>
      <p class="bw-fp-kicker">Площадки</p>
      <h2>Другие популярные свадьбы</h2>
    </div>
    <?php if (!empty($gallery_ids)) : ?>
      <div class="bw-fp-other-places__grid boxed">
        <?php foreach ($gallery_ids as $gallery_id) : ?>
          <article class="location-card">
            <a class="location-card__banner" href="<?php echo esc_url(get_permalink((int) $gallery_id)); ?>">
              <?php if (has_post_thumbnail((int) $gallery_id)) : ?>
                <?php echo get_the_post_thumbnail((int) $gallery_id, 'large', array('loading' => 'lazy')); ?>
              <?php endif; ?>
              <span class="location-card__title-wrap">
                <span class="location-card__title"><?php echo esc_html(get_the_title((int) $gallery_id)); ?></span>
              </span>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- ===================== GALLERY ===================== -->
  <?php if (!empty($fp_gallery_ids)) : ?>
  <section class="bw-fp-gallery full">
    <div class="bw-fp-gallery__grid">
      <?php foreach ($fp_gallery_ids as $gal_id) : ?>
        <?php $full_url = wp_get_attachment_image_url((int) $gal_id, 'full'); ?>
        <article class="location-card">
          <a class="location-card__banner svadba-gallery-item glightbox"
             href="<?php echo esc_url($full_url ?: '#'); ?>"
             data-gallery="fp-gallery">
            <?php echo wp_get_attachment_image((int) $gal_id, 'large', false, ['loading' => 'lazy', 'alt' => '']); ?>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ===================== CTA ===================== -->
  <section class="bw-fp-cta full" id="contact">
    <div class="bw-fp-cta__radial" aria-hidden="true"></div>
    <div class="bw-fp-cta__inner boxed">
      <p class="bw-fp-kicker">Начнём подготовку</p>
      <h2>Расскажите о вашей свадьбе —<br>мы предложим лучший сценарий</h2>
      <p>Заполните анкету или напишите нам в мессенджер. Первая консультация проходит в удобном для вас формате.</p>
      <div class="bw-fp-cta__actions">
        <a class="bw-fp-btn bw-fp-btn--main" href="/anketa/">Оставить заявку</a>
        <a class="bw-fp-btn bw-fp-btn--ghost" href="/kontakty/">Открыть контакты</a>
      </div>
    </div>
  </section>

</main>

<?php get_footer('v1'); ?>
