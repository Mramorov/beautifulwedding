<?php
/**
 * Custom Post Type: Service
 */
if (!defined('ABSPATH')) { exit; }

function bw_register_service_post_type() {
  $labels = array(
    'name'               => 'Services',
    'singular_name'      => 'Service',
    'menu_name'          => 'Services',
    'name_admin_bar'     => 'Service',
    'add_new'            => 'Add New',
    'add_new_item'       => 'Add New Service',
    'new_item'           => 'New Service',
    'edit_item'          => 'Edit Service',
    'view_item'          => 'View Service',
    'all_items'          => 'All Services',
    'search_items'       => 'Search Services',
    'parent_item_colon'  => 'Parent Services:',
    'not_found'          => 'No services found.',
    'not_found_in_trash' => 'No services found in Trash.',
  );

  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'has_archive'        => true,
    'rewrite'            => array('slug' => 'service'),
    'show_in_rest'       => true,
    'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions'),
    'menu_position'      => 20,
    'menu_icon'          => 'dashicons-clipboard',
  );

  register_post_type('service', $args);
}
add_action('init', 'bw_register_service_post_type');

