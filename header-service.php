<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <div class="svadba-hero-menu-center">
    <?php get_template_part('templates/main-menu'); ?>
  </div>
  <?php
  $post_id = get_queried_object_id();
  $featured_image_url = $post_id ? get_the_post_thumbnail_url($post_id, 'full') : '';
  ?>
  <header class="entry-header svadba-hero full" <?php if ($featured_image_url) : ?>style="background-image: url('<?php echo esc_url($featured_image_url); ?>');" <?php endif; ?>>
    <div class="svadba-hero-overlay falling-leaves"></div>
    <div class="svadba-hero-content">
      <?php get_template_part('templates/animated-title'); ?>
      <div class="empty-div"></div>
    </div>
    <svg width="0" height="0" style="position: absolute;">
      <defs>
        <!-- clipPathUnits="objectBoundingBox" — это МАГИЯ. 
         Она переключает координаты: 0 = 0%, 1 = 100% -->
        <clipPath id="wave-clip" clipPathUnits="objectBoundingBox">
          <path d="M 0,0 L 1,0 L 1,0.85 C 0.55,0.75 0.8,1 0,0.9 Z" />
        </clipPath>
      </defs>
    </svg>
  </header>