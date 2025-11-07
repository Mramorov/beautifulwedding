<?php
/**
 * Custom Post Types Registration
 */

if (!function_exists('beautifulwedding_register_post_types')):
    function beautifulwedding_register_post_types() {
        
        // Register Svadba Post Type
        $labels = array(
            'name'               => 'Svadby',
            'singular_name'      => 'Svadba',
            'menu_name'          => 'Svadby',
            'name_admin_bar'     => 'Svadba',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Svadba',
            'new_item'           => 'New Svadba',
            'edit_item'          => 'Edit Svadba',
            'view_item'          => 'View Svadba',
            'all_items'          => 'All Svadby',
            'search_items'       => 'Search Svadby',
            'parent_item_colon'  => 'Parent Svadby:',
            'not_found'          => 'No svadby found.',
            'not_found_in_trash' => 'No svadby found in Trash.'
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_nav_menus'  => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => array(
                'slug'       => 'svadba',
                'with_front' => false
            ),
            'has_archive'        => true,
            'hierarchical'       => false,
            'exclude_from_search'=> false,
            'capability_type'    => 'post',
            'menu_icon'          => 'dashicons-format-standard',
            'supports'           => array(
                'title',
                'editor',
                'thumbnail',
                'excerpt',
                'revisions',
                'custom-fields'
            )
        );

        register_post_type('svadba', $args);
    }
endif;

add_action('init', 'beautifulwedding_register_post_types');

// Register custom taxonomy 'places' for 'svadba'
add_action('init', function() {
    $labels = array(
        'name'              => 'Places',
        'singular_name'     => 'Place',
        'search_items'      => 'Search Places',
        'all_items'         => 'All Places',
        'parent_item'       => 'Parent Place',
        'parent_item_colon' => 'Parent Place:',
        'edit_item'         => 'Edit Place',
        'update_item'       => 'Update Place',
        'add_new_item'      => 'Add New Place',
        'new_item_name'     => 'New Place Name',
        'menu_name'         => 'Places',
    );
    $args = array(
        'hierarchical'      => true, // like categories
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'place'),
        'show_in_rest'      => true,
    );
    register_taxonomy('places', array('svadba'), $args);
});