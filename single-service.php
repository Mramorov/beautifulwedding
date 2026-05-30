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
<main id="post-<?php the_ID(); ?>" <?php post_class('layout service-single overflowed'); ?>>
  <section class="entry-content-wrapper shrink-animation boxed">
    <div class="entry-content">
      <?php the_content(); ?>
    </div>
    <?php
    $contextpic_id = get_post_meta(get_the_ID(), 'contextpic', true);
    if ($contextpic_id) : ?>
      <div class="entry-contextpic">
        <?php echo wp_get_attachment_image(intval($contextpic_id), 'large'); ?>
      </div>
    <?php endif; ?>
  </section>
  <?php
  $raw_tables_json = trim((string) get_post_meta(get_the_ID(), 'service_price_tables_json', true));
  $table_sections = array();

  if ($raw_tables_json !== '') {
    $decoded_sections = json_decode($raw_tables_json, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_sections)) {
      $table_sections = $decoded_sections;
    }
  }

  if (!empty($table_sections)) :
  ?>
    <section class="price-wrapper grow-animation boxed">
      <?php
      foreach ($table_sections as $section) {
        if (!is_array($section)) {
          continue;
        }

        $title = isset($section['title']) ? trim((string) $section['title']) : '';
        $key = isset($section['key']) ? trim((string) $section['key']) : '';
        if ($key === '' && isset($section['keys'])) {
          $keys_input = $section['keys'];
          if (is_string($keys_input)) {
            $key = trim((string) $keys_input);
          } elseif (is_array($keys_input) && !empty($keys_input)) {
            $key = trim((string) reset($keys_input));
          }
        }

        if ($key === '') {
          continue;
        }

        if (function_exists('bw_render_price_section')) {
          echo bw_render_price_section($title, array($key));
        } else {
          $service_shortcode = sprintf(
            '[bw_services keys="%s" title="%s"]',
            esc_attr($key),
            esc_attr($title)
          );
          echo do_shortcode($service_shortcode);
        }
      }
      ?>
    </section>
  <?php endif; ?>
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