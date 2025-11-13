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
          <h1 class="entry-title heading-xl"><?php the_title(); ?></h1>
          <div class="entry-meta text-accent">
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

        <?php
        // 1. Custom fields display
        $fromold = get_post_meta( get_the_ID(), 'fromold', true );
        $fromnew = get_post_meta( get_the_ID(), 'fromnew', true );
        $capacity = get_post_meta( get_the_ID(), 'capacity', true );
        $distance = get_post_meta( get_the_ID(), 'distance', true );
        $cer_time = get_post_meta( get_the_ID(), 'cer-time', true );

        if ( $fromold || $fromnew || $capacity || $distance || $cer_time ) : ?>
          <section class="svadba-custom-fields">
            <div class="custom-fields-grid">
              <?php if ( $fromold ) : ?>
                <div class="custom-field">
                  <span class="field-label text-accent">Старая цена (от):</span>
                  <span class="field-value heading-md"><?php echo esc_html( $fromold ); ?> €</span>
                </div>
              <?php endif; ?>

              <?php if ( $fromnew ) : ?>
                <div class="custom-field">
                  <span class="field-label text-accent">Новая цена (от):</span>
                  <span class="field-value heading-md"><?php echo esc_html( $fromnew ); ?> €</span>
                </div>
              <?php endif; ?>

              <?php if ( $capacity ) : ?>
                <div class="custom-field">
                  <span class="field-label">Вместимость:</span>
                  <span class="field-value"><?php echo esc_html( $capacity ); ?></span>
                </div>
              <?php endif; ?>

              <?php if ( $distance ) : ?>
                <div class="custom-field">
                  <span class="field-label">Базовое время автомобиля:</span>
                  <span class="field-value"><?php echo esc_html( $distance ); ?> ч.</span>
                </div>
              <?php endif; ?>

              <?php if ( $cer_time ) : ?>
                <div class="custom-field">
                  <span class="field-label">Время церемонии:</span>
                  <span class="field-value"><?php echo esc_html( $cer_time ); ?></span>
                </div>
              <?php endif; ?>
            </div>
          </section>
        <?php endif; ?>

        <?php // 2. Post content ?>
        <div class="entry-content">
          <?php the_content(); ?>
        </div>

        <?php
        // 4. Repeater data: Дополнительные залы и места
        if ( function_exists( 'get_svadba_repeater_data' ) ) {
            $repeater = get_svadba_repeater_data( get_the_ID() );
            if ( ! empty( $repeater ) && is_array( $repeater ) ) : ?>
              <section class="svadba-repeater">
                <h2 class="heading-lg">Дополнительные залы и места</h2>
                <div class="repeater-list">
                  <?php 
                  $index = 0;
                  foreach ( $repeater as $item ) : 
                    $mesto_name = ! empty( $item['mesto'] ) ? $item['mesto'] : '';
                    $place_price = ! empty( $item['place_price'] ) ? $item['place_price'] : 0;
                    $place_foto = ! empty( $item['place_foto'] ) ? $item['place_foto'] : 0;
                    $active_class = ( $index === 0 ) ? ' active-place' : '';
                  ?>
                    <div class="repeater-row place-item<?php echo $active_class; ?>" data-place-price="<?php echo esc_attr( $place_price ); ?>" data-place-name="<?php echo esc_attr( $mesto_name ); ?>">
                      <?php if ( $mesto_name ) : ?>
                        <div class="repeater-mesto">
                          <h3 class="heading-md"><?php echo esc_html( $mesto_name ); ?></h3>
                        </div>
                      <?php endif; ?>

                      <?php if ( $place_foto ) : ?>
                        <div class="repeater-image"><?php echo wp_get_attachment_image( intval( $place_foto ), 'medium' ); ?></div>
                      <?php endif; ?>

                      <?php if ( $place_price ) : ?>
                        <div class="repeater-price">
                          <span class="price-label">Добавленная цена:</span>
                          <span class="price-value"><?php echo esc_html( $place_price ); ?> €</span>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php 
                    $index++;
                  endforeach; ?>
                </div>
              </section>
            <?php endif;
        }
        ?>

        <?php // 3, 5 & 6. Svadba tabs: base package, form and packets ?>
        <section class="svadba-tabs-section">
          <div class="svadba-tabs-nav">
            <button type="button" class="svadba-tab-button btn btn-outline active" data-tab="base-tab">Базовый пакет</button>
            <button type="button" class="svadba-tab-button btn btn-outline" data-tab="individ-tab">Пакет "Под ключ"</button>
            <button type="button" class="svadba-tab-button btn btn-outline" data-tab="packets-tab">Готовые пакеты</button>
          </div>
          
          <div class="svadba-tabs-content">
            <div id="base-tab" class="svadba-tab-pane active">
              <?php
              // Display base package price
              $base_price = get_post_meta( get_the_ID(), 'fromnew', true );
              if ( $base_price ) : ?>
                <h3 class="main-place-price heading-lg text-center">Основной пакет услуг <span id="main-packet-sum" class="new-price" data-mainpacket-sum="<?php echo esc_attr( $base_price ); ?>"><?php echo esc_html( $base_price ); ?></span> <span class="kc-sign">€</span></h3>
                
                <?php
                // Include content from /base-packet page
                $base_packet_page = get_page_by_path( 'base-packet' );
                if ( $base_packet_page ) {
                  echo '<div class="base-packet-content">';
                  echo apply_filters( 'the_content', $base_packet_page->post_content );
                  echo '</div>';
                }
                ?>
                
                <div class="send-button-wrap"><button type="button" id="main_order_button" class="btn btn-large" data-formid="main">Заказать базовый пакет</button></div>
                <div id="main-message-form"></div>
              <?php endif; ?>
            </div>
            
            <div id="individ-tab" class="svadba-tab-pane">
              <?php echo do_shortcode('[svadba_form]'); ?>
            </div>
            
            <div id="packets-tab" class="svadba-tab-pane">
              <?php echo do_shortcode('[svadba_packets]'); ?>
            </div>
          </div>
        </section>

        <!-- Modal for order form -->
        <div class="modal-overlay" id="modalOverlay"></div>
        <div class="modal" id="modal">
          <div class="modal-header">
            <button class="close-button" id="closeButton">&times;</button>
          </div>
          <h5 class="modal-info-header heading-md text-center">Пожалуйста, укажите информацию для связи с Вами</h5>
          <form id="contactForm">
            <label for="name" class="form-label">Ваше имя:</label>
            <input type="text" id="name" name="Имя" class="form-input">
            <label for="email" class="form-label">Email:</label>
            <input type="email" id="email" name="email" class="form-input">
            <label for="phone" class="form-label">Телефон:</label>
            <input type="tel" id="phone" name="Телефон" class="form-input">
            <div class="modal-footer">
              <button type="button" id="cancelButton" class="btn btn-secondary">Отмена</button>
              <button type="button" id="sendButton" class="btn">Отправить</button>
              <div id="sending-process" class="process-send" style="display:none;">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/svadba/img/sending.gif' ); ?>" alt="Отправка..." width="28" height="28">
              </div>
            </div>
          </form>
          <div id="fill-error-mess" class="send-error"></div>
        </div>

        <?php
        // 7. Gallery (saved as array of attachment IDs in meta 'svadba_gallery')
        $gallery = get_post_meta( get_the_ID(), 'svadba_gallery', true );
        if ( ! empty( $gallery ) && is_array( $gallery ) ) : ?>
          <section class="svadba-gallery">
            <h2 class="heading-lg">Gallery</h2>
            <div class="svadba-gallery-grid">
              <?php foreach ( $gallery as $att_id ) :
                echo '<div class="svadba-gallery-item">' . wp_get_attachment_image( intval( $att_id ), 'large' ) . '</div>';
              endforeach; ?>
            </div>
          </section>
        <?php endif; ?>

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
