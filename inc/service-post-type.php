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

/**
 * Meta box: Service Preset (links to bw_services shortcode presets)
 */
function bw_service_add_meta_boxes() {
  add_meta_box(
    'bw_service_preset',
    'Service Preset',
    'bw_service_preset_metabox_render',
    'service',
    'side',
    'default'
  );
}
add_action('add_meta_boxes', 'bw_service_add_meta_boxes');

function bw_service_preset_metabox_render($post) {
  wp_nonce_field('bw_service_preset_save', 'bw_service_preset_nonce');
  $value = get_post_meta($post->ID, 'service_preset', true);
  $options = array(
    '' => '— Select preset —',
    'photo-video' => 'Photo & Video',
    'auto' => 'Automobiles',
    'hair-makeup' => 'Hair & Makeup',
    'flowers' => 'Flowers',
    'cakes' => 'Cakes',
    'dresses' => 'Dresses',
    'other' => 'Other Services',
  );
  echo '<select name="service_preset" id="service_preset" style="width:100%">';
  foreach ($options as $k => $label) {
    printf('<option value="%s" %s>%s</option>', esc_attr($k), selected($value, $k, false), esc_html($label));
  }
  echo '</select>';
}

function bw_service_preset_save($post_id) {
  if (!isset($_POST['bw_service_preset_nonce']) || !wp_verify_nonce($_POST['bw_service_preset_nonce'], 'bw_service_preset_save')) return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (isset($_POST['post_type']) && $_POST['post_type'] === 'service') {
    if (!current_user_can('edit_post', $post_id)) return;
  }
  $value = isset($_POST['service_preset']) ? sanitize_text_field($_POST['service_preset']) : '';
  update_post_meta($post_id, 'service_preset', $value);
}
add_action('save_post_service', 'bw_service_preset_save');

/**
 * Admin list columns for quick overview
 */
function bw_service_edit_columns($columns) {
  $columns['service_preset'] = 'Preset';
  return $columns;
}
add_filter('manage_service_posts_columns', 'bw_service_edit_columns');

function bw_service_custom_column($column, $post_id) {
  if ($column === 'service_preset') {
    $v = get_post_meta($post_id, 'service_preset', true);
    echo esc_html($v ?: '—');
  }
}
add_action('manage_service_posts_custom_column', 'bw_service_custom_column', 10, 2);
