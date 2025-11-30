<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <?php
  $post_id = get_queried_object_id();
  $featured_image_url = $post_id ? get_the_post_thumbnail_url($post_id, 'full') : '';
  ?>
  <header class="entry-header svadba-hero full" <?php if ($featured_image_url) : ?>style="background-image: url('<?php echo esc_url($featured_image_url); ?>');" <?php endif; ?>>
    <div class="svadba-hero-overlay falling-leaves"></div>
    <div class="svadba-hero-content">
      <div class="svadba-hero-menu-center">
        <?php get_template_part('navigation/main-menu'); ?>
      </div>
      <?php
      $title_text = get_the_title($post_id);
      $words = explode(' ', $title_text);
      $char_index = 0;
      ?>
      <h1 class="entry-title svadba-animated-title">
        <?php foreach ($words as $word_idx => $word): ?>
          <?php if ($word_idx > 0): ?><span class="word-space"> </span><?php endif; ?>
          <span class="word">
            <?php
            $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($chars as $ch):
            ?>
              <span class="char" style="--i: <?php echo (int)$char_index; ?>;">
                <?php echo esc_html($ch); ?>
              </span>
            <?php
              $char_index++;
            endforeach;
            ?>
          </span>
        <?php endforeach; ?>
      </h1>

      <?php
      $fromold = get_post_meta($post_id, 'fromold', true);
      $fromnew = get_post_meta($post_id, 'fromnew', true);
      ?>
      <div class="head-price-field">
        от:</span>
        <span class="old-price-value"><?php echo esc_html($fromold); ?> </span><span class="new-price-value"><?php echo esc_html($fromnew); ?> €</span>
      </div>

    </div>
  </header>