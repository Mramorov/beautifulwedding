<footer class="bw-footer bw-footer--v2" role="contentinfo">

  <div class="bw-footer-v2__inner boxed">

    <div class="bw-footer-v2__brand">
      <strong class="bw-footer-v2__name"><?php bloginfo('name'); ?></strong>
      <p class="bw-footer-v2__desc">
        Организуем свадьбы и торжества в Чехии.
        Помогаем влюблённым со всего мира создать
        незабываемый день в сказочной Праге.
      </p>
    </div>

    <div class="bw-footer-v2__contacts">
      <p class="bw-footer-v2__contacts-label">Связаться с нами</p>

      <div class="bw-footer-v2__contact-row">
        <a href="tel:+420773599143">+420 773 599 143</a>
        <span class="bw-footer-v2__icons">
          <a href="https://wa.me/420773599143" aria-label="WhatsApp">
            <img src="<?php echo get_template_directory_uri(); ?>/img/whatsapp-b.svg" alt="WhatsApp" width="18" height="18">
          </a>
          <a href="https://t.me/+420773599143" aria-label="Telegram">
            <img src="<?php echo get_template_directory_uri(); ?>/img/telegram-b.svg" alt="Telegram" width="18" height="18">
          </a>
        </span>
      </div>

      <div class="bw-footer-v2__contact-row">
        <a href="tel:+420773599148">+420 773 599 148</a>
        <span class="bw-footer-v2__icons">
          <a href="https://wa.me/420773599148" aria-label="WhatsApp">
            <img src="<?php echo get_template_directory_uri(); ?>/img/whatsapp-b.svg" alt="WhatsApp" width="18" height="18">
          </a>
          <a href="https://t.me/+420773599148" aria-label="Telegram">
            <img src="<?php echo get_template_directory_uri(); ?>/img/telegram-b.svg" alt="Telegram" width="18" height="18">
          </a>
        </span>
      </div>

      <a class="bw-footer-v2__email" href="mailto:cz@wedding-best.com">cz@wedding-best.com</a>
    </div>

  </div>

  <div class="bw-footer-v2__bottom">
    <div class="bw-footer-v2__bottom-inner boxed">
      <span>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?></span>
      <span>Прага, Чехия</span>
    </div>
  </div>

</footer>

<?php wp_footer(); ?>
</body>
</html>
