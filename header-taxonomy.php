<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <?php
  $queried_object = get_queried_object();
  $title_text = '';
  $featured_image_url = '';

  if ($queried_object instanceof WP_Term) {
    $title_text = $queried_object->name;
    $taxonomy_object = get_taxonomy($queried_object->taxonomy);
    $post_types = ($taxonomy_object && !empty($taxonomy_object->object_type))
      ? array_values((array) $taxonomy_object->object_type)
      : array('post');

    $hero_posts = get_posts(array(
      'post_type'      => $post_types,
      'post_status'    => 'publish',
      'posts_per_page' => 1,
      'orderby'        => array(
        'date' => 'DESC',
      ),
      'tax_query'      => array(
        array(
          'taxonomy' => $queried_object->taxonomy,
          'field'    => 'term_id',
          'terms'    => $queried_object->term_id,
        ),
      ),
      'no_found_rows'          => true,
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
    ));

    if (!empty($hero_posts)) {
      $featured_image_url = get_the_post_thumbnail_url((int) $hero_posts[0]->ID, 'full');
    }
  } elseif ($queried_object instanceof WP_Post_Type) {
    $post_type = (string) $queried_object->name;
    $title_text = !empty($queried_object->labels->name)
      ? (string) $queried_object->labels->name
      : (!empty($queried_object->label) ? (string) $queried_object->label : $post_type);

    $hero_posts = get_posts(array(
      'post_type'      => $post_type,
      'post_status'    => 'publish',
      'posts_per_page' => 1,
      'orderby'        => array(
        'date' => 'DESC',
      ),
      'no_found_rows'          => true,
      'update_post_meta_cache' => false,
      'update_post_term_cache' => false,
    ));

    if (!empty($hero_posts)) {
      $featured_image_url = get_the_post_thumbnail_url((int) $hero_posts[0]->ID, 'full');
    }
  }
  ?>

  <header class="entry-header svadba-hero full" <?php if ($featured_image_url) : ?>style="background-image: url('<?php echo esc_url($featured_image_url); ?>');" <?php endif; ?>>
    <div class="svadba-hero-overlay falling-leaves"></div>
    <div class="svadba-hero-content">
      <div class="svadba-hero-menu-center">
        <?php get_template_part('templates/main-menu'); ?>
      </div>
      <?php
      set_query_var('animated_title_text', $title_text);
      get_template_part('templates/animated-title');
      ?>
      <div class="empty-div"></div>
    </div>
    <svg width="0" height="0" style="position: absolute;">
      <defs>
        <clipPath id="wave-clip" clipPathUnits="objectBoundingBox">
          <path d="M 0,0 L 1,0 L 1,0.85 C 0.55,0.75 0.8,1 0,0.9 Z" />
        </clipPath>
      </defs>
    </svg>
  </header>
