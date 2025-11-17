<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class('header-svadba'); ?>>
  <div class="site">
    <?php
      $post_id = get_queried_object_id();
      $featured_image_url = $post_id ? get_the_post_thumbnail_url($post_id, 'full') : '';
    ?>
    <header class="entry-header svadba-hero" <?php if ($featured_image_url) : ?>style="background-image: url('<?php echo esc_url($featured_image_url); ?>');"<?php endif; ?>>
      <div class="svadba-hero-overlay"></div>
      <div class="svadba-hero-content">
        <nav class="site-nav" role="navigation">
          <?php
          wp_nav_menu(array(
            'theme_location' => 'primary',
            'container' => false,
            'menu_class' => '',
            'fallback_cb' => false,
          ));
          ?>
        </nav>
        <h1 class="entry-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
        <div class="entry-meta">
          <?php
          $terms = get_the_terms($post_id, 'location');
          if (! empty($terms) && ! is_wp_error($terms)) {
            $term_links = array();
            foreach ($terms as $term) {
              $term_links[] = '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
            }
            echo '<div class="svadba-locations">Локация: ' . implode(', ', $term_links) . '</div>';
          }
          ?>
        </div>
      </div>
    </header>
