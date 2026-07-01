<?php
/* ============================================================
 * AURORA EFFECT — эффект северного сияния в шапке
 * Чтобы отключить эффект, измените true на false
 * ============================================================ */
$aurora_enabled = false;
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php if ( $aurora_enabled ) : ?>
  <!-- AURORA EFFECT: стили -->
  <style>
    .svadba-hero .aurora-video {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      mix-blend-mode: hard-light;
      opacity: 0.3;
      pointer-events: none;
      z-index: 0;
    }
  </style>
  <?php endif; ?>
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
    <?php if ( $aurora_enabled ) : ?>
    <!-- AURORA EFFECT: видео с эффектом северного сияния -->
    <video class="aurora-video" autoplay loop muted playsinline
           onloadedmetadata="this.playbackRate=1">
      <source src="<?php echo get_template_directory_uri(); ?>/img/home-intro.mp4" type="video/mp4">
    </video>
    <!-- /AURORA EFFECT -->
    <?php endif; ?>
    <div class="svadba-hero-overlay falling-leaves"></div>
    <div class="svadba-hero-content">
      <div class="head-title-wrap">
        <?php get_template_part('templates/animated-title'); ?>
        <?php
        $fromold = get_post_meta($post_id, 'fromold', true);
        $fromnew = get_post_meta($post_id, 'fromnew', true);
        ?>
        <div class="head-price-field">
          <span>от:</span>
          <span class="old-price-value"><?php echo esc_html($fromold); ?> </span><span class="new-price-value"><?php echo esc_html($fromnew); ?> €</span>
        </div>
      </div>
      <div class="empty-div"></div>
    </div>
    <?php get_template_part('templates/wave-clip'); ?>
  </header>