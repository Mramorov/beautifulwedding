<?php
/**
 * Custom Post Type: Service
 */
if (!defined('ABSPATH')) { exit; }

function bw_register_service_post_type() {
  $labels = array(
    'name'               => 'Свадебные услуги',
    'singular_name'      => 'Услуга',
    'menu_name'          => 'Услуги',
    'name_admin_bar'     => 'Услуга',
    'add_new'            => 'Добавить',
    'add_new_item'       => 'Добавить услугу',
    'new_item'           => 'Новая услуга',
    'edit_item'          => 'Редактировать услугу',
    'view_item'          => 'Просмотреть услугу',
    'all_items'          => 'Все услуги',
    'search_items'       => 'Искать услуги',
    'parent_item_colon'  => 'Родительская услуга:',
    'not_found'          => 'Услуги не найдены.',
    'not_found_in_trash' => 'В корзине услуг не найдено.',
  );

  $args = array(
    'labels'             => $labels,
    'description'        => 'Свадебные услуги в Праге и Чехии: организация церемоний, координация в процессе подготовки и в день свадьбы, декор, фото- и видеосопровождение, а также дополнительные сервисы для комфортного праздника.',
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

