<?php

/** Minimal theme setup */

// Include Svadba post type and related functionality
require get_template_directory() . '/svadba/custom-post-types.php';
require get_template_directory() . '/svadba/custom-fields.php';
require get_template_directory() . '/svadba/custom-repeater.php';
require get_template_directory() . '/svadba/svadba.php';
require get_template_directory() . '/svadba/packets.php';

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
    $css_file = get_stylesheet_directory() . '/svadba/css/form.css';
    $js_file = get_stylesheet_directory() . '/svadba/js/form.js';
    
    $css_version = file_exists($css_file) ? filemtime($css_file) : wp_get_theme()->get('Version');
    $js_version = file_exists($js_file) ? filemtime($js_file) : wp_get_theme()->get('Version');

    wp_enqueue_style( 'svadba-form-style', get_stylesheet_directory_uri() . '/svadba/css/form.css', array( 'minimal-style' ), $css_version );
    // enqueue form behavior script
    wp_enqueue_script( 'svadba-form-script', get_stylesheet_directory_uri() . '/svadba/js/form.js', array( 'jquery' ), $js_version, true );
    // Localize for REST submission
    wp_localize_script( 'svadba-form-script', 'customFormParams', array(
      'restUrl' => esc_url_raw( rest_url( 'custom-form/v1/submit' ) ),
      'nonce'   => wp_create_nonce( 'wp_rest' ),
    ) );
  }
}
add_action('wp_enqueue_scripts', 'beautifulwedding_enqueue_svadba_assets');


/** REST endpoint for order submissions */
function beautifulwedding_handle_form_submission_api( WP_REST_Request $request ) {
  $form_data = $request->get_body_params();

  $subject   = 'Заказ с сайта';
  $subject_2 = 'Спасибо за Ваш заказ!';
  $message   = '<h2>Детали заказа:</h2>';

  foreach ( $form_data as $key => $value ) {
    // Remove [] suffix from label for display
    $label = str_replace( array('_', '[]'), array(' ', ''), $key );
    
    // Skip empty values
    if ( is_array( $value ) ) {
      // Filter out empty array items
      $value = array_filter( $value, function($v) { 
        return !empty($v) && $v !== 'Выберите...'; 
      });
      if ( empty( $value ) ) continue; // Skip if array is now empty
      
      $message .= "<br/>$label:<br/><br/><i> - " . implode('<br/> - ', array_map('esc_html', $value)) . '</i>';
    } else {
      $val = is_scalar($value) ? (string) $value : '';
      
      // Skip empty or placeholder values
      if ( empty($val) || $val === 'Выберите...' ) continue;
      
      // Formatting rules:
      // - Currency only for known price keys
      // - Hours for car_hours or labels containing "час"
      // - Never format phone/email as currency
      $currency_keys = array( 'Цена', 'services_sum', 'В том числе дополнительных услуг на сумму' );
      $hours_keys    = array( 'car_hours', 'Время автомобиля (час)' );
      $lower_label   = function_exists('mb_strtolower') ? mb_strtolower( $label, 'UTF-8' ) : strtolower( $label );

      if ( is_numeric( $val ) ) {
        if ( in_array( $key, $currency_keys, true ) || in_array( $label, $currency_keys, true ) ) {
          $val = number_format( (float) $val, 0, ',', ' ' ) . ' €';
        } elseif ( in_array( $key, $hours_keys, true ) || strpos( $lower_label, 'час' ) !== false ) {
          $val = number_format( (float) $val, 0, ',', ' ' ) . ' ч.';
        } elseif ( $key === 'Телефон' || $label === 'Телефон' || $key === 'email' || $label === 'email' ) {
          $val = esc_html( $val );
        } else {
          // Default: plain number without currency
          $val = esc_html( $val );
        }
      } else {
        $val = esc_html( $val );
      }
      $message .= "<br/>$label: <i>$val</i><br/>";
      if ( $key === 'Телефон' ) {
        $message .= '<br/><hr/><br/>';
      }
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
