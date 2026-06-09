<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <?php
  $term = get_queried_object();
  $term_name = ($term instanceof WP_Term) ? $term->name : '';
  $hero_bg_url = '';

  if ($term instanceof WP_Term) {
    $hero_posts = get_posts(array(
      'post_type'      => 'svadba',
      'posts_per_page' => 1,
      'orderby'        => array(
        'date'       => 'DESC',
      ),
      'tax_query'      => array(
        array(
          'taxonomy' => $term->taxonomy,
          'field'    => 'term_id',
          'terms'    => $term->term_id,
        ),
      ),
    ));

    if (!empty($hero_posts)) {
      $hero_bg_url = get_the_post_thumbnail_url((int) $hero_posts[0]->ID, 'full');
    }
  }
  $featured_image_url = $hero_bg_url;
  ?>

  <header class="entry-header svadba-hero full" <?php if ($featured_image_url) : ?>style="background-image: url('<?php echo esc_url($featured_image_url); ?>');" <?php endif; ?>>
    <div class="svadba-hero-overlay falling-leaves"></div>
    <div class="svadba-hero-content">
      <div class="svadba-hero-menu-center">
        <?php get_template_part('templates/main-menu'); ?>
      </div>
      <?php
      // Pass taxonomy name into the animated title template.
      set_query_var('animated_title_text', $term_name);
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
