<?php

/**
 * Template Name: Contacts Page
 * Template Post Type: page
 * Description: Шаблон страницы контактов Wedding Best.
 */

if (!defined('ABSPATH')) {
  exit;
}

get_header('front-page');
?>

<main id="post-<?php the_ID(); ?>" <?php post_class('layout contacts-page overflowed'); ?>>
  <section class="contacts-cta">
    <p> Опишите свои пожелания, и мы с радостью поможем воплотить вашу мечту в реальность. </p>
    <?php get_template_part('template-parts/docs'); ?>

    <div class="contacts-copyright">
      <a class="button-alt" href="/anketa-vstupjushhih-v-brak/">Заполнить анкету</a>
    </div>
  </section>
  <section class="contacts-intro">
    <div>
      <p>Если у Вас намечается свадьба, юбилей свадьбы или другое радостное событие и Вы хотели бы провести это время в сказочной Чехии, просто позвоните или напишите нам.</p>
      <p>По вопросам сотрудничества также обращайтесь к нам любым удобным способом. Мы всегда рады общению.</p>

    </div>
  </section>
  <section class="contacts-form-wrap">
    <h2>Напишите нам</h2>
    <p>Оставьте заявку, и мы свяжемся с вами в ближайшее время.</p>

    <form class="contacts-ajax-form" id="contactsAjaxForm" novalidate>
      <div class="contacts-form-grid">
        <label>
          Имя
          <input type="text" name="name" required>
        </label>
        <label>
          Телефон
          <input type="text" name="phone" required>
        </label>
      </div>

      <label>
        E-mail
        <input type="email" name="email" required>
      </label>

      <label>
        Сообщение
        <textarea name="message" rows="5" required></textarea>
      </label>

      <label class="contacts-form-consent">
        <input type="checkbox" name="consent" value="1" required>
        <span>Согласен(а) на обработку персональных данных</span>
      </label>

      <input type="text" name="website" value="" autocomplete="off" tabindex="-1" class="contacts-hp" aria-hidden="true">

      <div class="contacts-form-actions">
        <button type="submit" class="button-main">Отправить</button>
        <p class="contacts-form-status" id="contactsFormStatus" aria-live="polite"></p>
      </div>
    </form>
  </section>


  <section class="contacts-detail">
    <div>
      <article class="contacts-card">
        <div class="contact-col">
          <div>
            Телефоны:
          </div>
          <div class="tel-row">
            <a href="tel:+420773599143"><img class="whatsappimg" src="/wp-content/themes/beautifulwedding/img/whatsapp-b.svg" /></a>
            <a href="tel:+420773599143"><img src="/wp-content/themes/beautifulwedding/img/telegram-b.svg" /></a>
            <a href="tel:+420773599143">+420 773 599 143</a>
          </div>
          <div class="tel-row">
            <a href="tel:+420773599148"><img class="whatsappimg" src="/wp-content/themes/beautifulwedding/img/whatsapp-b.svg" /></a>
            <a href="tel:+420773599148"><img src="/wp-content/themes/beautifulwedding/img/telegram-b.svg" /></a>
            <a href="tel:+420773599148">+420 773 599 148</a>
          </div>
        </div>

        <div class="contact-col contact-col-mail">
          <div>
            E-mail:
          </div>
          <div>
            <a href="mailto:cz@wedding-best.com">cz@wedding-best.com</a>
          </div>
          <div>
            <a href="mailto:wbestprague@gmail.com">wbestprague@gmail.com</a>
          </div>
        </div>
      </article>

      <article class="address-card separate-text">
        <h3>Адрес и время работы офиса</h3>
        <p>
          <a href="https://goo.gl/maps/Eq9nNmnJzo32" target="_blank" rel="noopener">Hybernska 1007/20, 110 00 Praha 1, Ceska republika</a>
        </p>
        <p>
          Пн-Вс: приём по предварительной записи<br>Мы для Вас на связи 24/7
        </p>
      </article>
    </div>
  </section>

</main>

<?php get_footer('contacts'); ?>