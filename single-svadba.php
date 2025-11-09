<?php
/**
 * Template for single Svadba posts
 * File: single-svadba.php
 */

get_header();
?>

<main class="site-main" id="main">
  <div class="content-area">
    <?php while ( have_posts() ) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('svadba-single'); ?>>
        <header class="entry-header">
          <h1 class="entry-title"><?php the_title(); ?></h1>
          <div class="entry-meta">
            <?php
            // Show terms from 'places' taxonomy
            $terms = get_the_terms( get_the_ID(), 'places' );
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                $term_links = array();
                foreach ( $terms as $term ) {
                    $term_links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
                }
                echo '<div class="svadba-places">Places: ' . implode( ', ', $term_links ) . '</div>';
            }
            ?>
          </div>
        </header>

        <div class="entry-content">
          <?php echo wp_kses_post( apply_filters( 'the_content', get_the_content() ) ); ?>
        </div>

        <?php
        // Gallery (saved as array of attachment IDs in meta 'svadba_gallery')
        $gallery = get_post_meta( get_the_ID(), 'svadba_gallery', true );
        if ( ! empty( $gallery ) && is_array( $gallery ) ) : ?>
          <section class="svadba-gallery">
            <h2>Gallery</h2>
            <div class="svadba-gallery-grid">
              <?php foreach ( $gallery as $att_id ) :
                echo '<div class="svadba-gallery-item">' . wp_get_attachment_image( intval( $att_id ), 'large' ) . '</div>';
              endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

        <?php
        // Repeater data (helper get_svadba_repeater_data)
        if ( function_exists( 'get_svadba_repeater_data' ) ) {
            $repeater = get_svadba_repeater_data( get_the_ID() );
            if ( ! empty( $repeater ) && is_array( $repeater ) ) : ?>
              <section class="svadba-repeater">
                <h2>Additional Information</h2>
                <div class="repeater-list">
                  <?php foreach ( $repeater as $item ) : ?>
                    <div class="repeater-row">
                      <?php if ( ! empty( $item['text'] ) ) : ?>
                        <div class="repeater-text"><?php echo esc_html( $item['text'] ); ?></div>
                      <?php endif; ?>

                      <?php if ( ! empty( $item['image'] ) ) : ?>
                        <div class="repeater-image"><?php echo wp_get_attachment_image( intval( $item['image'] ), 'medium' ); ?></div>
                      <?php endif; ?>

                      <?php if ( isset( $item['number'] ) ) : ?>
                        <div class="repeater-number"><?php echo intval( $item['number'] ); ?></div>
                      <?php endif; ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </section>
            <?php endif;
        }
        ?>

      </article>

      <?php
      // Comments template if you want
      if ( comments_open() || get_comments_number() ) :
        comments_template();
      endif;
      ?>

    <?php endwhile; ?>
  </div>
</main>

<?php get_footer();
