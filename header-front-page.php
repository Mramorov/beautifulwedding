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
  /*
   * bw_fp_custom_hero — если установлен, шаблон страницы сам строит hero-секцию.
   * В этом случае стандартный svadba-hero заголовок пропускается.
   */
  if (!get_query_var('bw_fp_custom_hero')) :
    $post_id = get_queried_object_id();
    $featured_image_url = $post_id ? get_the_post_thumbnail_url($post_id, 'full') : '';
  ?>
  <header class="entry-header svadba-hero full" <?php if ($featured_image_url) : ?>style="background-image: url('<?php echo esc_url($featured_image_url); ?>');" <?php endif; ?>>
    <div class="svadba-hero-overlay falling-leaves"></div>
    <div class="svadba-hero-content">
      <?php get_template_part('templates/animated-title'); ?>
      <div class="empty-div"></div>
    </div>
    <?php get_template_part('templates/wave-clip'); ?>
  </header>
  <?php endif; ?>
