<?php

/**
 * Taxonomy template for location terms.
 */
if (!defined('ABSPATH')) {
  exit;
}

get_header('taxonomy');

$term = get_queried_object();
if (!($term instanceof WP_Term)) {
  get_footer();
  return;
}

/**
 * Load static map config for supported location terms.
 */
function bw_get_location_map_config($term_slug, $supported_slugs)
{
  $config = array(
    'view' => array(),
    'coordinates' => array(),
    'enabled' => false,
  );

  if (!in_array($term_slug, $supported_slugs, true)) {
    return $config;
  }

  $coords_file = get_template_directory() . '/assets/data/location/' . $term_slug . '.json';
  if (!file_exists($coords_file)) {
    return $config;
  }

  $raw_coords = file_get_contents($coords_file);
  if ($raw_coords === false) {
    return $config;
  }

  $decoded_coords = json_decode($raw_coords, true);
  if (!is_array($decoded_coords)) {
    return $config;
  }

  if (isset($decoded_coords['view']) && is_array($decoded_coords['view'])) {
    $config['view'] = $decoded_coords['view'];
  }

  if (isset($decoded_coords['points']) && is_array($decoded_coords['points'])) {
    $config['coordinates'] = $decoded_coords['points'];
    $config['enabled'] = true;
  }

  return $config;
}

/* Removed unused: bw_get_location_card_size_class */

function bw_get_map_point_from_coordinates($post_id, $location_coordinates)
{
  $coords = isset($location_coordinates[(string) $post_id]) && is_array($location_coordinates[(string) $post_id])
    ? $location_coordinates[(string) $post_id]
    : array();
  return array(
    'id'      => $post_id,
    'title'   => get_the_title($post_id),
    'url'     => get_permalink($post_id),
    'image'   => get_the_post_thumbnail_url($post_id, 'thumbnail') ?: '',
    'lat'     => isset($coords['lat']) ? (float) $coords['lat'] : null,
    'lng'     => isset($coords['lng']) ? (float) $coords['lng'] : null,
  );
}


function bw_render_banner_card($post_id)
{
?>
  <article class="location-card">
    <a class="location-card__banner" href="<?php echo esc_url(get_permalink($post_id)); ?>">
      <?php if (has_post_thumbnail($post_id)) : ?>
        <?php echo get_the_post_thumbnail($post_id, 'large', array('loading' => 'lazy')); ?>
      <?php endif; ?>
      <span class="location-card__title-wrap">
        <span class="location-card__title"><?php echo esc_html(get_the_title($post_id)); ?></span>
      </span>
    </a>
  </article>
<?php
}


function bw_get_location_gallery_ids($post_id)
{
  $gallery = get_post_meta($post_id, 'svadba_gallery', true);
  if (!is_array($gallery)) {
    return array();
  }

  return array_values(array_filter(array_map('intval', $gallery)));
}

function bw_render_location_best_offer_card($post_id)
{
  $post_id = (int) $post_id;
  if ($post_id <= 0) {
    return;
  }

  $permalink = get_permalink($post_id);
  if ($permalink === false) {
    return;
  }

  $title = get_the_title($post_id);
  $capacity = trim((string) get_post_meta($post_id, 'capacity', true));
  $excerpt = trim((string) get_post_field('post_excerpt', $post_id));

  $featured_image_id = (int) get_post_thumbnail_id($post_id);
  $gallery_ids = bw_get_location_gallery_ids($post_id);
  $gallery_image_1 = isset($gallery_ids[0]) ? (int) $gallery_ids[0] : 0;
  $gallery_image_2 = isset($gallery_ids[1]) ? (int) $gallery_ids[1] : 0;
?>
  <article class="location-best-offer-card">
    <div class="location-best-offer-card__text-cell">
      <?php if ($excerpt !== '') : ?>
        <?php echo  $excerpt; ?>
      <?php endif; ?>
      <a class="button button-main" href="<?php echo esc_url($permalink); ?>">Узнать подробнее</a>
    </div>
    <a class="location-best-offer-card__main location-card__banner" href="<?php echo esc_url($permalink); ?>">
      <?php if ($featured_image_id > 0) : ?>
        <?php echo wp_get_attachment_image($featured_image_id, 'large', false, array('loading' => 'lazy')); ?>
      <?php else : ?>
        <span class="location-best-offer-card__placeholder" aria-hidden="true"></span>
      <?php endif; ?>
      <span class="location-card__title-wrap">
        <span class="location-card__title"><?php echo esc_html($title); ?></span>
      </span>
    </a>

    <a class="location-best-offer-card__side location-best-offer-card__side--capacity location-card__banner" href="<?php echo esc_url($permalink); ?>">
      <?php if ($gallery_image_1 > 0) : ?>
        <?php echo wp_get_attachment_image($gallery_image_1, 'large', false, array('loading' => 'lazy')); ?>
      <?php else : ?>
        <span class="location-best-offer-card__placeholder" aria-hidden="true"></span>
      <?php endif; ?>
      <span class="location-card__title-wrap">
        <span class="location-card__title"><?php echo esc_html($capacity !== '' ? 'до ' . $capacity . ' гостей' : ''); ?></span>
      </span>
    </a>

    <a class="location-best-offer-card__side location-best-offer-card__side--excerpt location-card__banner" href="<?php echo esc_url($permalink); ?>">
      <?php if ($gallery_image_2 > 0) : ?>
        <?php echo wp_get_attachment_image($gallery_image_2, 'large', false, array('loading' => 'lazy')); ?>
      <?php else : ?>
        <span class="location-best-offer-card__placeholder" aria-hidden="true"></span>
      <?php endif; ?>
      <?php $cer_time = trim((string) get_post_meta($post_id, 'cer-time', true)); ?>
      <span class="location-card__title-wrap">
        <span class="location-card__title"><?php echo esc_html($cer_time !== '' ? 'Время: ' . $cer_time : ''); ?></span>
      </span>
    </a>
  </article>
<?php
}

$location_map_supported_slugs = array('svadba-v-prage', 'svadba-v-zamke-chehii');
$location_map_config = bw_get_location_map_config($term->slug, $location_map_supported_slugs);
$location_map_view = $location_map_config['view'];
$location_coordinates = $location_map_config['coordinates'];
$location_map_enabled = $location_map_config['enabled'];

// Disable responsive srcset/sizes in this template to keep a single explicit image source.
add_filter('wp_calculate_image_srcset', '__return_false');

$base_tax_query = array(
  array(
    'taxonomy' => 'location',
    'field'    => 'term_id',
    'terms'    => $term->term_id,
  ),
);

$base_location_query_args = array(
  'post_type'      => 'svadba',
  'orderby'        => array(
    'menu_order' => 'ASC',
    'date'       => 'DESC',
  ),
  'tax_query'      => $base_tax_query,
);

$ordered_location_post_ids = get_posts(array_merge(
  $base_location_query_args,
  array(
    'posts_per_page'         => -1,
    'fields'                 => 'ids',
    'no_found_rows'          => true,
    'update_post_meta_cache' => true,
    'update_post_term_cache' => false,
  )
));

$popular_post_ids = array();
$best_offer_post_id = 0;
$remaining_post_ids = array();

foreach ($ordered_location_post_ids as $post_id) {
  $menu_order = (int) get_post_field('menu_order', $post_id);
  if ($menu_order >= 1 && $menu_order <= 6) {
    $popular_post_ids[] = (int) $post_id;
  } elseif ($menu_order === 7) {
    $best_offer_post_id = (int) $post_id;
  } else {
    $remaining_post_ids[] = (int) $post_id;
  }
}

$map_points = array();
if ($location_map_enabled) {
  foreach ($ordered_location_post_ids as $post_id) {
    $map_points[] = bw_get_map_point_from_coordinates((int) $post_id, $location_coordinates);
  }
}
?>

<main class="taxonomy-location-main overflowed">
  <div class="description_wrap grow-animation">
    <div class="location-description-intro">
      <?php if (!empty(trim((string) $term->description))) : ?>
        <?php echo $term->description; ?>
      <?php endif; ?>
    </div>
  </div>

  <section class="location-section location-section--popular shrink-animation">
    <h2 class="location-section-title">Популярные локации</h2>
    <div class="location-popular-grid">
      <?php foreach ($popular_post_ids as $post_id) : ?>
        <?php bw_render_banner_card((int) $post_id); ?>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="location-section location-section--best-offer grow-animation">
    <h2 class="location-section-title">Лучшее предложение</h2>
    <?php if ($best_offer_post_id > 0) : ?>
      <?php bw_render_location_best_offer_card($best_offer_post_id); ?>
    <?php endif; ?>
  </section>

  <section class="location-section location-section--all">
    <h2 class="location-section-title">Другие локации</h2>
    <div class="location-grid--all">
      <?php foreach ($remaining_post_ids as $post_id) : ?>
        <?php bw_render_banner_card((int) $post_id); ?>
      <?php endforeach; ?>
    </div>
  </section>
  <?php if ($location_map_enabled) : ?>
    <div class="description_wrap">
      <div class="location-description-intro">
        <p>Планируете свадьбу в Чехии и хотите быстро найти подходящую площадку? Воспользуйтесь интерактивной картой, на которой собраны все свадебные локации этой категории. Она поможет оценить расположение площадок и подобрать наиболее удобный вариант. С карты можно сразу перейти на страницу интересующей вас локации, чтобы ознакомиться с её описанием, фотографиями и особенностями. Вы также можете открыть выбранное место на Google Maps и воспользоваться навигацией, построением маршрута и другими возможностями сервиса.
        </p>
      </div>
      <div class="location-map-toolbar">
        <a href="#" class="location-map-open-link" data-open-location-map>Показать карту мест</a>
      </div>
    </div>
    <div class="location-map-modal" id="location-map-modal" hidden>
      <div class="location-map-modal__backdrop" data-close-location-map></div>
      <div class="location-map-modal__dialog" role="dialog" aria-modal="true" aria-label="Карта локаций">
        <button type="button" class="location-map-modal__close" aria-label="Закрыть" data-close-location-map>&times;</button>
        <div class="location-map-canvas" id="location-map-canvas"></div>
      </div>
    </div>

    <script id="bw-location-map-data" type="application/json">
      <?php echo wp_json_encode(array('view' => $location_map_view, 'points' => $map_points), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    </script>
  <?php endif; ?>
</main>

<?php remove_filter('wp_calculate_image_srcset', '__return_false'); ?>

<?php get_footer(); ?>