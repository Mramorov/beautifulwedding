<?php

/**
 * Generic taxonomy template.
 * Used as fallback for svadba taxonomies except dedicated taxonomy-location.php.
 */
if (!defined('ABSPATH')) {
  exit;
}

$term = get_queried_object();
$taxonomy_object = ($term instanceof WP_Term) ? get_taxonomy($term->taxonomy) : false;
$is_svadba_taxonomy = $taxonomy_object && in_array('svadba', (array) $taxonomy_object->object_type, true);

if ($is_svadba_taxonomy) {
  get_header('location');
} else {
  get_header();
}

if (!($term instanceof WP_Term)) {
  get_footer();
  return;
}

if (!function_exists('bw_render_taxonomy_banner_card')) {
  function bw_render_taxonomy_banner_card($post_id)
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

$post_ids = array();

if ($is_svadba_taxonomy) {
  $post_ids = get_posts(array(
    'post_type'      => 'svadba',
    'posts_per_page' => -1,
    'orderby'        => array(
      'menu_order' => 'ASC',
      'date'       => 'DESC',
    ),
    'tax_query'      => array(
      array(
        'taxonomy' => $term->taxonomy,
        'field'    => 'term_id',
        'terms'    => $term->term_id,
      ),
    ),
    'fields'                 => 'ids',
    'no_found_rows'          => true,
    'update_post_meta_cache' => true,
    'update_post_term_cache' => false,
  ));
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

  <section class="location-section location-section--all shrink-animation">
    <h2 class="location-section-title"><?php echo esc_html($taxonomy_object && isset($taxonomy_object->labels->name) ? $taxonomy_object->labels->name : 'Локации'); ?></h2>
    <div class="location-grid--all">
      <?php if (!empty($post_ids)) : ?>
        <?php foreach ($post_ids as $post_id) : ?>
          <?php bw_render_taxonomy_banner_card((int) $post_id); ?>
        <?php endforeach; ?>
      <?php else : ?>
        <p>Для этого раздела пока нет материалов.</p>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>