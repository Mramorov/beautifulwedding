<?php

/**
 * Single Service template
 */

// Табличные стили для цен (используются шорткодом [bw_services])
$tables_css = get_template_directory() . '/assets/css/price-tables.css';
$tables_ver = file_exists($tables_css) ? filemtime($tables_css) : wp_get_theme()->get('Version');
wp_enqueue_style('bw-price-tables', get_template_directory_uri() . '/assets/css/price-tables.css', array('minimal-style'), $tables_ver);

get_header('service');
?>
<main id="post-<?php the_ID(); ?>" <?php post_class('layout service-single'); ?>>
  <section class="service-content boxed">
    <div class="entry-content">
      <?php while (have_posts()) : the_post();
        the_content();
      endwhile; ?>
    </div>
  </section>

  <?php
    // Галерея (GLightbox). Используем общее метаполе 'svadba_gallery' и для свадеб, и для услуг.
    $gallery = get_post_meta(get_the_ID(), 'svadba_gallery', true);
    if (! empty($gallery) && is_array($gallery)) :
      $gallery_id = 'svadba-gallery-' . get_the_ID();
    ?>
      <section class="svadba-gallery full">
        <div class="svadba-gallery-grid" id="<?php echo esc_attr($gallery_id); ?>">
          <?php
          foreach ($gallery as $att_id) {
            $full = wp_get_attachment_image_src(intval($att_id), 'full');
            $thumb_html = wp_get_attachment_image(intval($att_id), 'medium_large', false, array('loading' => 'lazy'));
            if ($full) {
              $url = $full[0];
              echo '<a href="' . esc_url($url) . '" class="svadba-gallery-item glightbox" data-gallery="' . esc_attr($gallery_id) . '">' . $thumb_html . '</a>';
            }
          }
          ?>
        </div>
      </section>
    <?php endif; ?>
</main>
<?php get_footer(); ?>