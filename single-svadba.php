<?php

/**
 * Template for single Svadba posts
 * File: single-svadba.php
 */

get_header('svadba');
?>

<main class="site-main" id="main">
  <div class="content-area">
    <?php while (have_posts()) : the_post(); ?>
      <article id="post-<?php the_ID(); ?>" <?php post_class('svadba-single'); ?>>

        <?php
        // 1. Custom fields display
        $fromold = get_post_meta(get_the_ID(), 'fromold', true);
        $fromnew = get_post_meta(get_the_ID(), 'fromnew', true);
        $capacity = get_post_meta(get_the_ID(), 'capacity', true);
        $distance = get_post_meta(get_the_ID(), 'distance', true);
        $cer_time = get_post_meta(get_the_ID(), 'cer-time', true);

        if ($fromold || $fromnew || $capacity || $distance || $cer_time) : ?>
          <section class="svadba-custom-fields">
            <div class="custom-fields-grid">
              <?php if ($fromold) : ?>
                <div class="custom-field">
                  <span class="field-label">Старая цена (от):</span>
                  <span class="field-value"><?php echo esc_html($fromold); ?> €</span>
                </div>
              <?php endif; ?>

              <?php if ($fromnew) : ?>
                <div class="custom-field">
                  <span class="field-label">Новая цена (от):</span>
                  <span class="field-value"><?php echo esc_html($fromnew); ?> €</span>
                </div>
              <?php endif; ?>

              <?php if ($capacity) : ?>
                <div class="custom-field">
                  <span class="field-label">Вместимость:</span>
                  <span class="field-value"><?php echo esc_html($capacity); ?></span>
                </div>
              <?php endif; ?>

              <?php if ($distance) : ?>
                <div class="custom-field">
                  <span class="field-label">Базовое время автомобиля:</span>
                  <span class="field-value"><?php echo esc_html($distance); ?> ч.</span>
                </div>
              <?php endif; ?>

              <?php if ($cer_time) : ?>
                <div class="custom-field">
                  <span class="field-label">Время церемонии:</span>
                  <span class="field-value"><?php echo esc_html($cer_time); ?></span>
                </div>
              <?php endif; ?>
            </div>
          </section>
        <?php endif; ?>

        <?php // 2. Post content with contextpic image 
        $contextpic_id = get_post_meta(get_the_ID(), 'contextpic', true);
        ?>
        <div class="entry-content-wrapper">
          <div class="entry-content">
            <?php the_content(); ?>
          </div>
          <?php if ($contextpic_id) : ?>
            <div class="entry-contextpic">
              <?php echo wp_get_attachment_image(intval($contextpic_id), 'large'); ?>
            </div>
          <?php endif; ?>
        </div>

        <?php
        // 4. Repeater data: Дополнительные залы и места
        if (function_exists('get_svadba_repeater_data')) {
          $repeater = get_svadba_repeater_data(get_the_ID());
          if (! empty($repeater) && is_array($repeater)) : ?>
            <section class="svadba-repeater">
              <h2>Дополнительные залы и места</h2>
              <p>(выбор места может влиять на цену пакета)</p>
              <div class="repeater-list">
                <?php
                $index = 0;
                foreach ($repeater as $item) :
                  $mesto_name = ! empty($item['mesto']) ? $item['mesto'] : '';
                  $place_price = ! empty($item['place_price']) ? $item['place_price'] : 0;
                  $place_foto = ! empty($item['place_foto']) ? $item['place_foto'] : 0;
                  $active_class = ($index === 0) ? ' active-place' : '';
                ?>
                  <div class="repeater-row place-item<?php echo $active_class; ?>" data-place-price="<?php echo esc_attr($place_price); ?>" data-place-name="<?php echo esc_attr($mesto_name); ?>">
                    <?php if ($mesto_name) : ?>
                      <div class="repeater-mesto">
                        <h3><?php echo esc_html($mesto_name); ?></h3>
                      </div>
                    <?php endif; ?>

                    <?php if ($place_foto) : ?>
                      <div class="repeater-image"><?php echo wp_get_attachment_image(intval($place_foto), 'medium'); ?></div>
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

        <?php // 3, 5 & 6. Svadba tabs: base package, form and packets 
        ?>
        <section class="svadba-tabs-section">
          <div class="svadba-tabs-nav">
            <button type="button" class="svadba-tab-button active" data-tab="base-tab">Базовый пакет</button>
            <button type="button" class="svadba-tab-button" data-tab="packets-tab">Готовые пакеты</button>
            <button type="button" class="svadba-tab-button" data-tab="individ-tab">Пакет "Под ключ"</button>
          </div>

          <div class="svadba-tabs-content">
            <div id="base-tab" class="svadba-tab-pane active">
              <?php
              // Display base package price
              $base_price = get_post_meta(get_the_ID(), 'fromnew', true);
              if ($base_price) : ?>
                <h3 class="main-place-price">Основной пакет услуг <span id="main-packet-sum" class="new-price" data-mainpacket-sum="<?php echo esc_attr($base_price); ?>"><?php echo esc_html($base_price); ?></span> <span class="kc-sign">€</span></h3>

                <?php get_template_part('template-parts/svadba-block'); ?>
                
                <div class="send-button-wrap"><button type="button" id="main_order_button" class="button-main" data-formid="main">Заказать базовый пакет</button></div>
                <div id="main-message-form"></div>
              <?php endif; ?>
            </div>

            <div id="packets-tab" class="svadba-tab-pane">
              <?php echo do_shortcode('[svadba_packets]'); ?>
            </div>            

            <div id="individ-tab" class="svadba-tab-pane">
              <?php echo do_shortcode('[svadba_form]'); ?>
            </div>

          </div>
        </section>
        <?php get_template_part('template-parts/self-pay'); ?>

        <!-- Modal for order form -->
        <div class="modal-overlay" id="modalOverlay"></div>
        <div class="modal" id="modal">
          <div class="modal-header">
            <button class="close-button" id="closeButton">&times;</button>
          </div>
          <h5 class="modal-info-header">Пожалуйста, укажите информацию для связи с Вами</h5>
          <form id="contactForm">
            <label for="name">Ваше имя:</label>
            <input type="text" id="name" name="Имя">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email">
            <label for="phone">Телефон:</label>
            <input type="tel" id="phone" name="Телефон">
            <div class="modal-footer">
              <button type="button" id="cancelButton" class="button-alt">Отмена</button>
              <button type="button" id="sendButton" class="button-main">Отправить</button>
              <div id="sending-process" class="process-send" style="display:none;">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/img/sending.gif'); ?>" alt="Отправка..." width="28" height="28">
              </div>
            </div>
          </form>
          <div id="fill-error-mess" class="send-error"></div>
        </div>

        <?php
        // 7. Галерея с PhotoSwipe 5 (мета 'svadba_gallery' хранит ID вложений)
        $gallery = get_post_meta(get_the_ID(), 'svadba_gallery', true);
        if (! empty($gallery) && is_array($gallery)) :
          $gallery_id = 'svadba-gallery-' . get_the_ID();
        ?>
          <section class="svadba-gallery">
            <h2>Галерея</h2>
            <div class="svadba-gallery-grid" id="<?php echo esc_attr($gallery_id); ?>">
              <?php
              foreach ($gallery as $att_id) {
                $full = wp_get_attachment_image_src(intval($att_id), 'full');
                $thumb_html = wp_get_attachment_image(intval($att_id), 'large', false, array('loading' => 'lazy'));
                if ($full) {
                  $url = $full[0]; $w = (int) $full[1]; $h = (int) $full[2];
                  echo '<a href="' . esc_url($url) . '" data-pswp-width="' . esc_attr($w) . '" data-pswp-height="' . esc_attr($h) . '" class="svadba-gallery-item" data-pswp-gallery="' . esc_attr($gallery_id) . '">' . $thumb_html . '</a>';
                }
              }
              ?>
            </div>
          </section>
        <?php endif; ?>

      </article>

      <?php
      // Comments template if you want
      if (comments_open() || get_comments_number()) :
        comments_template();
      endif;
      ?>

    <?php endwhile; ?>
  </div>
</main>

<?php get_footer();
