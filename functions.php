<?php

/** Minimal theme setup */

/** Reverting svadba CPT and related functionality to always load */
require_once get_template_directory() . '/inc/cpt/svadba-post-type.php';
//Для работы метабоксов свадбы в админке
require_once get_template_directory() . '/inc/meta-fields/svadba-fields.php';
require_once get_template_directory() . '/inc/meta-fields/svadba-repeater.php';
require_once get_template_directory() . '/inc/meta-fields/svadba-gallery.php';
//для словаря меток. надо пересмотреть
require_once get_template_directory() . '/inc/utils/svadba-common.php'; 
/* require_once get_template_directory() . '/inc/utils/svadba-packets.php';
require_once get_template_directory() . '/inc/utils/svadba-main.php';*/

// Unified services config + shortcodes
// Service CPT
require_once get_template_directory() . '/inc/config/services-config.php';
require_once get_template_directory() . '/inc/shortcodes/shortcodes-services.php';
require_once get_template_directory() . '/inc/cpt/service-post-type.php';
// Anketa feature files
require_once get_template_directory() . '/anketa/anketa-handler.php'; //для инициализации REST API анкеты


function beautifulwedding_setup()
{
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', array('search-form', 'gallery', 'caption'));
  register_nav_menus(array(
    'primary' => 'Primary Menu',
  ));
}
add_action('after_setup_theme', 'beautifulwedding_setup');


function beautifulwedding_scripts()
{
  $style_path = get_stylesheet_directory() . '/style.css';
  $style_ver  = file_exists($style_path) ? filemtime($style_path) : wp_get_theme()->get('Version');
  wp_enqueue_style('minimal-style', get_stylesheet_uri(), array(), $style_ver);

  $footer_css = get_stylesheet_directory() . '/assets/css/footer.css';
  $footer_css_ver = file_exists($footer_css) ? filemtime($footer_css) : $style_ver;
  wp_enqueue_style('bw-footer', get_stylesheet_directory_uri() . '/assets/css/footer.css', array('minimal-style'), $footer_css_ver);

  // Подключение стилей и скриптов мега-меню
  $menu_css = get_stylesheet_directory() . '/assets/css/mega-menu.css';
  $menu_js  = get_stylesheet_directory() . '/assets/js/mega-menu.js';
  $menu_css_ver = file_exists($menu_css) ? filemtime($menu_css) : $style_ver;
  $menu_js_ver  = file_exists($menu_js) ? filemtime($menu_js) : $style_ver;
  wp_enqueue_style('bw-mega-menu', get_stylesheet_directory_uri() . '/assets/css/mega-menu.css', array('minimal-style'), $menu_css_ver);
  wp_enqueue_script('bw-mega-menu', get_stylesheet_directory_uri() . '/assets/js/mega-menu.js', array('jquery'), $menu_js_ver, true);

  // Подключение стилей главной страницы
  if (is_front_page()) {
    $front_css = get_stylesheet_directory() . '/assets/css/front-page.css';
    $front_css_ver = file_exists($front_css) ? filemtime($front_css) : $style_ver;
    wp_enqueue_style('bw-front-page', get_stylesheet_directory_uri() . '/assets/css/front-page.css', array('minimal-style'), $front_css_ver);
    $front_js = get_stylesheet_directory() . '/assets/js/front-page.js';
    $front_js_ver = file_exists($front_js) ? filemtime($front_js) : $style_ver;
    wp_enqueue_script('bw-front-page', get_stylesheet_directory_uri() . '/assets/js/front-page.js', array(), $front_js_ver, true);
  }

  // Стили страницы контактов.
  if (is_page('kontakty')) {
    $contacts_css = get_stylesheet_directory() . '/assets/css/contacts.css';
    $contacts_js = get_stylesheet_directory() . '/assets/js/contacts-form.js';
    $contacts_css_ver = file_exists($contacts_css) ? filemtime($contacts_css) : $style_ver;
    $contacts_js_ver = file_exists($contacts_js) ? filemtime($contacts_js) : $style_ver;
    wp_enqueue_style('bw-contacts-page', get_stylesheet_directory_uri() . '/assets/css/contacts.css', array('minimal-style'), $contacts_css_ver);
    wp_enqueue_script('bw-contacts-form', get_stylesheet_directory_uri() . '/assets/js/contacts-form.js', array(), $contacts_js_ver, true);
    wp_localize_script('bw-contacts-form', 'bwContactsForm', array(
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('bw_contacts_form_nonce'),
    ));
  }

  // Общие стили таксономий и service-архива
  if (is_tax() || is_post_type_archive('service')) {
    $taxonomy_css = get_stylesheet_directory() . '/assets/css/taxonomy.css';
    $taxonomy_css_ver = file_exists($taxonomy_css) ? filemtime($taxonomy_css) : $style_ver;
    wp_enqueue_style('bw-taxonomy', get_stylesheet_directory_uri() . '/assets/css/taxonomy.css', array('minimal-style'), $taxonomy_css_ver);
  }

  // Специфичные стили только для taxonomy-location
  if (is_tax('location')) {
    $location_css = get_stylesheet_directory() . '/assets/css/taxonomy-location.css';
    $location_css_ver = file_exists($location_css) ? filemtime($location_css) : $style_ver;
    wp_enqueue_style('bw-taxonomy-location', get_stylesheet_directory_uri() . '/assets/css/taxonomy-location.css', array('bw-taxonomy'), $location_css_ver);
  }

  // Карта нужна только для location
  if (is_tax('location')) {
    $leaflet_css = get_stylesheet_directory() . '/assets/css/vendor/leaflet.css';
    $leaflet_js = get_stylesheet_directory() . '/assets/js/vendor/leaflet.js';
    $location_map_js = get_stylesheet_directory() . '/assets/js/taxonomy-location-map.js';
    $leaflet_css_ver = file_exists($leaflet_css) ? filemtime($leaflet_css) : $style_ver;
    $leaflet_js_ver = file_exists($leaflet_js) ? filemtime($leaflet_js) : $style_ver;
    $location_map_js_ver = file_exists($location_map_js) ? filemtime($location_map_js) : $style_ver;

    wp_enqueue_style('bw-leaflet', get_stylesheet_directory_uri() . '/assets/css/vendor/leaflet.css', array('minimal-style'), $leaflet_css_ver);
    wp_enqueue_script('bw-leaflet', get_stylesheet_directory_uri() . '/assets/js/vendor/leaflet.js', array(), $leaflet_js_ver, true);
    wp_enqueue_script('bw-taxonomy-location-map', get_stylesheet_directory_uri() . '/assets/js/taxonomy-location-map.js', array('bw-leaflet'), $location_map_js_ver, true);
  }
}
add_action('wp_enqueue_scripts', 'beautifulwedding_scripts');

/**
 * Enqueue Svadba form styles when needed (single svadba or when shortcode present)
 */
function beautifulwedding_enqueue_svadba_assets() {
  // Only load on frontend
  if ( is_admin() ) {
    return;
  }

  $load = false;

  if ( function_exists( 'is_singular' ) && is_singular( 'svadba' ) ) {
    $load = true;
  } else {
    // check for shortcode in post content when on singular page
    global $post;
    if ( isset( $post ) && has_shortcode( $post->post_content, 'svadba_form' ) ) {
      $load = true;
    }
  }

  if ( $load ) {
    // Get file modification times for versioning
    $css_file = get_stylesheet_directory() . '/assets/css/svadba.css';
    $js_file = get_stylesheet_directory() . '/assets/js/svadba.js';
    
    $css_version = file_exists($css_file) ? filemtime($css_file) : wp_get_theme()->get('Version');
    $js_version = file_exists($js_file) ? filemtime($js_file) : wp_get_theme()->get('Version');

    wp_enqueue_style( 'svadba-form-style', get_stylesheet_directory_uri() . '/assets/css/svadba.css', array( 'minimal-style' ), $css_version );
    // enqueue form behavior script
    wp_enqueue_script( 'svadba-form-script', get_stylesheet_directory_uri() . '/assets/js/svadba.js', array( 'jquery' ), $js_version, true );
    // Localize for REST submission
    wp_localize_script( 'svadba-form-script', 'customFormParams', array(
      'restUrl' => esc_url_raw( rest_url( 'custom-form/v1/submit' ) ),
      'nonce'   => wp_create_nonce( 'wp_rest' ),
    ) );
    // Localize pricing coefficients for JS parity
    global $bw_pricing;
    if ( isset( $bw_pricing ) ) {
      wp_localize_script( 'svadba-form-script', 'bwPricing', $bw_pricing );
    }
  }
}
add_action('wp_enqueue_scripts', 'beautifulwedding_enqueue_svadba_assets');


/** REST endpoint for order submissions */
function beautifulwedding_handle_form_submission_api( WP_REST_Request $request ) {
  $form_data = $request->get_body_params();
  $labels = function_exists('svadba_get_labels') ? svadba_get_labels() : array();

  $subject   = 'Заказ с сайта';
  $subject_2 = 'Спасибо за Ваш заказ!';
  $message   = '<h2>Детали заказа:</h2>';

  // Ключи полей, значения которых форматируем как валюту
  $currency_keys = array( 'price', 'services_sum' );
  foreach ( $form_data as $key => $value ) {
    $label = isset($labels[$key]) ? $labels[$key] : $key;

    if ( is_array( $value ) ) {
      $filtered = array_filter( $value );
      if ( empty( $filtered ) ) { continue; }
      $message .= "<br/>$label:<br/><br/><i> - " . implode('<br/> - ', array_map('esc_html', $filtered)) . '</i>';
      continue;
    }

    $val = is_scalar($value) ? trim( (string) $value ) : '';
    if ( $val === '' ) { continue; }

    if ( is_numeric( $val ) ) {
      if ( in_array( $key, $currency_keys, true ) ) {
        $formatted = number_format( (float) $val, 0, ',', ' ' ) . ' €';
      } else {
        $formatted = esc_html( $val );
      }
    } else {
      $formatted = esc_html( $val );
    }

    $message .= "<br/>$label: <i>$formatted</i><br/>";
    if ( $key === 'Телефон' ) {
      $message .= '<br/><hr/><br/>';
    }
  }

  $admin_email = get_option( 'admin_email' );
  $headers = array(
    'Content-Type: text/html; charset=UTF-8',
    'From: Wedding-best (Свадьба в Праге) <cz@wedding-best.com>',
  );

  $sent_admin = wp_mail( $admin_email, $subject, $message, $headers );
  $sent_user  = ! empty( $form_data['email'] ) ? wp_mail( $form_data['email'], $subject_2, $message, $headers ) : false;

  if ( $sent_admin || $sent_user ) {
    return new WP_REST_Response( array( 'success' => true ), 200 );
  }
  return new WP_REST_Response( array( 'success' => false ), 500 );
}

add_action( 'rest_api_init', function() {
  register_rest_route( 'custom-form/v1', '/submit', array(
    'methods'  => 'POST',
    'callback' => 'beautifulwedding_handle_form_submission_api',
    'permission_callback' => function () {
      return isset($_SERVER['HTTP_X_WP_NONCE']) && wp_verify_nonce( $_SERVER['HTTP_X_WP_NONCE'], 'wp_rest' );
    }
  ) );
} );

/** Enqueue GLightbox assets */
function beautifulwedding_enqueue_lightbox_assets() {
  if ( ! ( is_singular( 'svadba' ) || is_singular( 'service' ) || is_front_page() ) ) {
    return;
  }

  // GLightbox from CDN
  wp_enqueue_style(
    'glightbox',
    'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css',
    array(),
    '3.3.0'
  );

  wp_enqueue_script(
    'glightbox',
    'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js',
    array(),
    '3.3.0',
    true
  );

  wp_enqueue_script(
    'beautifulwedding-glightbox-init',
    get_template_directory_uri() . '/assets/js/glightbox-init.js',
    array( 'glightbox' ),
    filemtime( get_template_directory() . '/assets/js/glightbox-init.js' ),
    true
  );

}
add_action( 'wp_enqueue_scripts', 'beautifulwedding_enqueue_lightbox_assets' );

/** AJAX handler for contacts page form submission */
function beautifulwedding_submit_contacts_form() {
  check_ajax_referer('bw_contacts_form_nonce', 'nonce');

  $website = isset($_POST['website']) ? trim((string) wp_unslash($_POST['website'])) : '';
  if ($website !== '') {
    wp_send_json_success(array('message' => 'ok'));
  }

  $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
  $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
  $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
  $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
  $consent = isset($_POST['consent']) ? (string) wp_unslash($_POST['consent']) : '';

  if ($name === '' || $phone === '' || $email === '' || $message === '' || $consent !== '1') {
    wp_send_json_error(array('message' => 'Заполните все обязательные поля.'), 400);
  }

  if (!is_email($email)) {
    wp_send_json_error(array('message' => 'Некорректный e-mail.'), 400);
  }

  $admin_email = get_option('admin_email');
  if (!$admin_email || !is_email($admin_email)) {
    wp_send_json_error(array('message' => 'Не настроен e-mail администратора сайта.'), 500);
  }

  $subject = 'Новая заявка со страницы контактов';
  $body = "Имя: {$name}\n";
  $body .= "Телефон: {$phone}\n";
  $body .= "E-mail: {$email}\n\n";
  $body .= "Сообщение:\n{$message}\n";

  $headers = array(
    'Content-Type: text/plain; charset=UTF-8',
    'Reply-To: ' . $name . ' <' . $email . '>',
  );

  $sent = wp_mail($admin_email, $subject, $body, $headers);
  if (!$sent) {
    wp_send_json_error(array('message' => 'Ошибка отправки письма.'), 500);
  }

  wp_send_json_success(array('message' => 'Сообщение отправлено.'));
}
add_action('wp_ajax_bw_submit_contacts_form', 'beautifulwedding_submit_contacts_form');
add_action('wp_ajax_nopriv_bw_submit_contacts_form', 'beautifulwedding_submit_contacts_form');


