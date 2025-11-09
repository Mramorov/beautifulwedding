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


// Small helpers
function minimal_classic_excerpt_more($more)
{
  return '...';
}
add_filter('excerpt_more', 'minimal_classic_excerpt_more');
