<?php

/** Minimal theme setup */

// Include Svadba post type and related functionality
require get_template_directory() . '/svadba/custom-post-types.php';
require get_template_directory() . '/svadba/custom-fields.php';
require get_template_directory() . '/svadba/custom-repeater.php';
require get_template_directory() . '/svadba/svadba.php';

function minimal_classic_setup()
{
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', array('search-form', 'gallery', 'caption'));
  register_nav_menus(array(
    'primary' => 'Primary Menu',
  ));
}
add_action('after_setup_theme', 'minimal_classic_setup');


function minimal_classic_scripts()
{
  wp_enqueue_style('minimal-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'minimal_classic_scripts');

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
  }
}
add_action('wp_enqueue_scripts', 'beautifulwedding_enqueue_svadba_assets');


// Small helpers
function minimal_classic_excerpt_more($more)
{
  return '...';
}
add_filter('excerpt_more', 'minimal_classic_excerpt_more');
