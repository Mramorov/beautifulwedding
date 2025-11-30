<?php
/**
 * Single Service template
 */
get_header();
?>
<main id="post-<?php the_ID(); ?>" <?php post_class('layout service-single'); ?>>
  <section class="service-hero boxed">
    <h1 class="service-title"><?php the_title(); ?></h1>
    <?php if (has_post_thumbnail()) : ?>
      <div class="service-thumbnail">
        <?php the_post_thumbnail('large'); ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="service-content boxed">
    <div class="entry-content">
      <?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
    </div>
  </section>

  <?php
  // Render preset tables if configured
  $preset = get_post_meta(get_the_ID(), 'service_preset', true);
  if (!empty($preset)) : ?>
  <section class="service-prices boxed">
    <?php echo do_shortcode('[bw_services preset="' . esc_attr($preset) . '"]'); ?>
  </section>
  <?php endif; ?>
</main>
<?php get_footer(); ?>
