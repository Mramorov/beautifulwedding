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
  $term_description = ($term instanceof WP_Term) ? term_description($term) : '';
  $hero_bg_url = '';

  if ($term instanceof WP_Term) {
    $hero_posts = get_posts(array(
      'post_type'      => 'svadba',
      'posts_per_page' => 1,
      'orderby'        => array(
        'menu_order' => 'ASC',
        'date'       => 'DESC',
      ),
      'tax_query'      => array(
        array(
          'taxonomy' => 'location',
          'field'    => 'term_id',
          'terms'    => $term->term_id,
        ),
      ),
    ));

    if (!empty($hero_posts)) {
      $hero_bg_url = get_the_post_thumbnail_url((int) $hero_posts[0]->ID, 'full');
    }
  }
  ?>

  <header class="entry-header svadba-hero full" <?php if ($hero_bg_url) : ?>style="background-image: url('<?php echo esc_url($hero_bg_url); ?>');" <?php endif; ?>>
    <div class="svadba-hero-overlay"></div>
    <div class="svadba-hero-content">
      <div class="svadba-hero-menu-center">
        <?php get_template_part('templates/main-menu'); ?>
      </div>
      <div class="head-title-wrap taxonomy-location-hero-text">
        <h1 class="entry-title"><?php echo esc_html($term_name); ?></h1>
        <?php if (!empty(trim(wp_strip_all_tags($term_description)))) : ?>
          <div class="taxonomy-location-description">
            <?php echo wp_kses_post($term_description); ?>
          </div>
        <?php endif; ?>
      </div>
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
