<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class('anketa-form-page'); ?>>
    <header class="site-header anketa-header boxed">
      <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></h1>

      <nav class="site-nav" role="navigation">
        <?php
        // Можно отключить навигацию при необходимости: закомментируйте wp_nav_menu.
        // wp_nav_menu(array(
        //   'theme_location' => 'primary',
        //   'container' => false,
        //   'menu_class' => '',
        //   'fallback_cb' => false,
        // ));
        ?>
      </nav>
    </header>
