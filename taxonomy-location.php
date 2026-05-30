<?php

/**
 * Taxonomy template for location terms.
 */
if (!defined('ABSPATH')) {
  exit;
}

get_header('location');

$term = get_queried_object();
if (!($term instanceof WP_Term)) {
  get_footer();
  return;
}

/**
 * Render venue details under each banner.
 */
function bw_render_location_card_meta($post_id)
{
  $fromnew = get_post_meta($post_id, 'fromnew', true);
  $capacity = get_post_meta($post_id, 'capacity', true);
  $characteristics = get_post_meta($post_id, 'characteristics', true);
  $ceremonies = wp_get_post_terms($post_id, 'ceremonies', array('fields' => 'names'));

  echo '<div class="location-card-meta">';

  $excerpt = get_the_excerpt($post_id);
  if (!empty($excerpt)) {
    echo '<p class="location-card-excerpt">' . esc_html($excerpt) . '</p>';
  }

  if (!empty($ceremonies) && !is_wp_error($ceremonies)) {
    echo '<p><strong>Церемония:</strong> ' . esc_html(implode(', ', $ceremonies)) . '</p>';
  }

  if ($fromnew !== '') {
    echo '<p><strong>Цена от:</strong> ' . esc_html($fromnew) . ' €</p>';
  }

  if ($capacity !== '') {
    echo '<p><strong>Вместимость:</strong> до ' . esc_html($capacity) . '</p>';
  }

  if (!empty($characteristics)) {
    echo '<div class="location-card-characteristics">' . wp_kses_post($characteristics) . '</div>';
  }

  echo '</div>';
}

$popular_query = new WP_Query(array(
  'post_type'      => 'svadba',
  'posts_per_page' => 3,
  'orderby'        => array(
    'menu_order' => 'ASC',
    'date'       => 'DESC',
  ),
  'menu_order'     => 0,
  'tax_query'      => array(
    array(
      'taxonomy' => 'location',
      'field'    => 'term_id',
      'terms'    => $term->term_id,
    ),
  ),
));

$all_query = new WP_Query(array(
  'post_type'      => 'svadba',
  'posts_per_page' => -1,
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

$other_terms = get_terms(array(
  'taxonomy'   => 'location',
  'hide_empty' => false,
  'exclude'    => array($term->term_id),
));
?>

<main class="layout taxonomy-location-main overflowed">
  <section class="location-section boxed">
    <h2 class="location-section-title">Популярные направления</h2>
    <div class="location-grid location-grid--popular">
      <?php if ($popular_query->have_posts()) : ?>
        <?php while ($popular_query->have_posts()) : $popular_query->the_post(); ?>
          <?php $post_id = get_the_ID(); ?>
          <article class="location-card location-card--popular">
            <a class="location-card-banner" href="<?php the_permalink(); ?>">
              <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
              <?php endif; ?>
            </a>
            <h3 class="location-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <?php bw_render_location_card_meta($post_id); ?>
          </article>
        <?php endwhile; ?>
      <?php endif; ?>
      <?php wp_reset_postdata(); ?>
    </div>
  </section>

  <section class="location-text-gap boxed">
    <div class="location-text-gap-inner">
      <?php echo wp_kses_post(term_description($term)); ?>
    </div>
  </section>

  <section class="location-section boxed">
    <h2 class="location-section-title">Остальные направления</h2>
    <div class="location-grid location-grid--mixed">
      <?php if ($all_query->have_posts()) : ?>
        <?php while ($all_query->have_posts()) : $all_query->the_post(); ?>
          <?php
          $post_id = get_the_ID();
          $weight = (string) get_post_field('menu_order', $post_id);
          if ($weight === '0') {
            continue;
          }

          $size_class = 'location-card--11';
          if (in_array($weight, array('11', '21', '12', '22'), true)) {
            $size_class = 'location-card--' . $weight;
          }
          ?>
          <article class="location-card <?php echo esc_attr($size_class); ?>">
            <a class="location-card-banner" href="<?php the_permalink(); ?>">
              <?php if (has_post_thumbnail()) : ?>
                <?php the_post_thumbnail('large', array('loading' => 'lazy')); ?>
              <?php endif; ?>
            </a>
            <h3 class="location-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
            <?php bw_render_location_card_meta($post_id); ?>
          </article>
        <?php endwhile; ?>
      <?php endif; ?>
      <?php wp_reset_postdata(); ?>
    </div>
  </section>

  <section class="location-section boxed">
    <h2 class="location-section-title">Другие локации</h2>
    <div class="location-grid location-grid--other-terms">
      <?php
      $shown = 0;
      foreach ($other_terms as $other_term) :
        if ($shown >= 2) {
          break;
        }

        $term_posts = get_posts(array(
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
              'terms'    => $other_term->term_id,
            ),
          ),
        ));

        $banner = '';
        if (!empty($term_posts)) {
          $banner = get_the_post_thumbnail_url((int) $term_posts[0]->ID, 'large');
        }
        ?>
        <article class="location-card location-card--term-link">
          <a class="location-card-banner" href="<?php echo esc_url(get_term_link($other_term)); ?>">
            <?php if (!empty($banner)) : ?>
              <img src="<?php echo esc_url($banner); ?>" alt="<?php echo esc_attr($other_term->name); ?>" loading="lazy">
            <?php endif; ?>
          </a>
          <h3 class="location-card-title"><a href="<?php echo esc_url(get_term_link($other_term)); ?>"><?php echo esc_html($other_term->name); ?></a></h3>
          <?php if (!empty(trim(wp_strip_all_tags($other_term->description)))) : ?>
            <p class="location-card-excerpt"><?php echo esc_html(wp_strip_all_tags($other_term->description)); ?></p>
          <?php endif; ?>
        </article>
        <?php
        $shown++;
      endforeach;
      ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
