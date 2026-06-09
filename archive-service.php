<?php

/**
 * Archive template for Service CPT.
 */
if (!defined('ABSPATH')) {
  exit;
}

get_header('taxonomy');

if (!function_exists('bw_render_service_archive_card')) {
  function bw_render_service_archive_card($post_id)
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
}

$post_ids = get_posts(array(
  'post_type'              => 'service',
  'post_status'            => 'publish',
  'posts_per_page'         => -1,
  'orderby'                => array(
    'menu_order' => 'ASC',
    'date'       => 'DESC',
  ),
  'fields'                 => 'ids',
  'no_found_rows'          => true,
  'update_post_meta_cache' => false,
  'update_post_term_cache' => false,
));

$post_type_object = get_post_type_object('service');
$archive_title = $post_type_object && !empty($post_type_object->labels->name)
  ? $post_type_object->labels->name
  : post_type_archive_title('', false);
$archive_description = $post_type_object && !empty($post_type_object->description)
  ? $post_type_object->description
  : '';
?>

<main class="taxonomy-location-main overflowed service-archive-main">
  <div class="description_wrap grow-animation">
    <div class="location-description-intro">
      <?php if ($archive_description !== '') : ?>
        <?php echo wp_kses_post(wpautop($archive_description)); ?>
      <?php endif; ?>
    </div>
  </div>

  <section class="location-section location-section--all shrink-animation">
    <h1 class="location-section-title"><?php echo esc_html($archive_title); ?></h1>
    <div class="location-grid--all">
      <?php if (!empty($post_ids)) : ?>
        <?php foreach ($post_ids as $post_id) : ?>
          <?php bw_render_service_archive_card((int) $post_id); ?>
        <?php endforeach; ?>
      <?php else : ?>
        <p>Для этого раздела пока нет материалов.</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
