<?php

/** Minimal theme setup */

// Include Svadba post type and related functionality
require get_template_directory() . '/svadba/custom-post-types.php';
require get_template_directory() . '/svadba/custom-fields.php';
require get_template_directory() . '/svadba/custom-repeater.php';
require get_template_directory() . '/svadba/svadba.php';
require get_template_directory() . '/svadba/packets.php';
// Unified services config + shortcodes
require_once get_template_directory() . '/inc/services-config.php';
require_once get_template_directory() . '/inc/shortcodes-services.php';
// Service CPT
require_once get_template_directory() . '/inc/service-post-type.php';
// Anketa feature files
require_once get_template_directory() . '/anketa/common.php';
require_once get_template_directory() . '/anketa/anketa-handler.php';

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

  // Подключение стилей и скриптов мега-меню
  $menu_css = get_stylesheet_directory() . '/navigation/mega-menu.css';
  $menu_js  = get_stylesheet_directory() . '/navigation/mega-menu.js';
  $menu_css_ver = file_exists($menu_css) ? filemtime($menu_css) : $style_ver;
  $menu_js_ver  = file_exists($menu_js) ? filemtime($menu_js) : $style_ver;
  wp_enqueue_style('bw-mega-menu', get_stylesheet_directory_uri() . '/navigation/mega-menu.css', array('minimal-style'), $menu_css_ver);
  wp_enqueue_script('bw-mega-menu', get_stylesheet_directory_uri() . '/navigation/mega-menu.js', array('jquery'), $menu_js_ver, true);
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
    $css_file = get_stylesheet_directory() . '/svadba/svadba.css';
    $js_file = get_stylesheet_directory() . '/svadba/svadba.js';
    
    $css_version = file_exists($css_file) ? filemtime($css_file) : wp_get_theme()->get('Version');
    $js_version = file_exists($js_file) ? filemtime($js_file) : wp_get_theme()->get('Version');

    wp_enqueue_style( 'svadba-form-style', get_stylesheet_directory_uri() . '/svadba/svadba.css', array( 'minimal-style' ), $css_version );
    // enqueue form behavior script
    wp_enqueue_script( 'svadba-form-script', get_stylesheet_directory_uri() . '/svadba/svadba.js', array( 'jquery' ), $js_version, true );
    // Localize for REST submission
    wp_localize_script( 'svadba-form-script', 'customFormParams', array(
      'restUrl' => esc_url_raw( rest_url( 'custom-form/v1/submit' ) ),
      'nonce'   => wp_create_nonce( 'wp_rest' ),
    ) );
    // Localize pricing coefficients for JS parity
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

/** Enqueue PhotoSwipe assets for svadba single pages */
function beautifulwedding_enqueue_photoswipe_assets() {
    if ( ! is_singular( 'svadba' ) ) {
        return;
    }

    wp_enqueue_style(
        'photoswipe',
        'https://unpkg.com/photoswipe@5/dist/photoswipe.css',
        array(),
        '5.4.4'
    );

    wp_enqueue_script(
        'photoswipe',
        'https://unpkg.com/photoswipe@5/dist/umd/photoswipe.umd.min.js',
        array(),
        '5.4.4',
        true
    );

    wp_enqueue_script(
        'photoswipe-lightbox',
        'https://unpkg.com/photoswipe@5/dist/umd/photoswipe-lightbox.umd.min.js',
        array( 'photoswipe' ),
        '5.4.4',
        true
    );

    wp_enqueue_script(
      'beautifulwedding-photoswipe-init',
      get_template_directory_uri() . '/svadba/photoswipe-init.js',
      array( 'photoswipe-lightbox' ),
      filemtime( get_template_directory() . '/svadba/photoswipe-init.js' ),
      true
    );
}
add_action( 'wp_enqueue_scripts', 'beautifulwedding_enqueue_photoswipe_assets' );

add_action('template_redirect', function () {

    // Админы всегда видят сайт
    if ( current_user_can('manage_options') ) {
        return;
    }

    // Список разрешённых URL (slug, путь или ID)
    $allowed = [
        '/anketa-vstupjushhih-v-brak'        // страница анкеты
    ];

    $current_path = strtok($_SERVER['REQUEST_URI'], '?'); // без GET-параметров

    // Разрешаем доступ, если путь совпадает
    foreach ($allowed as $path) {
        if (rtrim($current_path, '/') === rtrim($path, '/')) {
            return;
        }
    }

    // Всё остальное: режим обслуживания
    wp_die(
        '<h1>Сайт в стадии разработки</h1><p>Ориентировочная дата запуска: 10.01.2026</p>',
        'Обслуживание',
        ['response' => 503]
    );
});

