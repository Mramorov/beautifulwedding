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

// Register custom taxonomy 'location' for 'svadba' (hierarchical, like categories)
add_action('init', function() {
    $labels = array(
        'name'              => 'Locations',
        'singular_name'     => 'Location',
        'search_items'      => 'Search Locations',
        'all_items'         => 'All Locations',
        'parent_item'       => 'Parent Location',
        'parent_item_colon' => 'Parent Location:',
        'edit_item'         => 'Edit Location',
        'update_item'       => 'Update Location',
        'add_new_item'      => 'Add New Location',
        'new_item_name'     => 'New Location Name',
        'menu_name'         => 'Locations',
    );
    $args = array(
        'hierarchical'      => true, // hierarchical, like categories
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'location'),
        'show_in_rest'      => true,
    );
    register_taxonomy('location', array('svadba'), $args);
});

// Register custom taxonomy 'wedding_days' for 'svadba' (hierarchical, like categories)
add_action('init', function() {
    $labels = array(
        'name'              => 'Wedding Days',
        'singular_name'     => 'Wedding Day',
        'search_items'      => 'Search Wedding Days',
        'all_items'         => 'All Wedding Days',
        'parent_item'       => 'Parent Wedding Day',
        'parent_item_colon' => 'Parent Wedding Day:',
        'edit_item'         => 'Edit Wedding Day',
        'update_item'       => 'Update Wedding Day',
        'add_new_item'      => 'Add New Wedding Day',
        'new_item_name'     => 'New Wedding Day Name',
        'menu_name'         => 'Wedding Days',
    );
    $args = array(
        'hierarchical'      => true, // hierarchical, like categories
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'wedding-day'),
        'show_in_rest'      => true,
    );
    register_taxonomy('wedding_days', array('svadba'), $args);
});

// Register custom taxonomy 'ceremonies' for 'svadba' (hierarchical, like categories)
add_action('init', function() {
    $labels = array(
        'name'              => 'Ceremonies',
        'singular_name'     => 'Ceremony',
        'search_items'      => 'Search Ceremonies',
        'all_items'         => 'All Ceremonies',
        'parent_item'       => 'Parent Ceremony',
        'parent_item_colon' => 'Parent Ceremony:',
        'edit_item'         => 'Edit Ceremony',
        'update_item'       => 'Update Ceremony',
        'add_new_item'      => 'Add New Ceremony',
        'new_item_name'     => 'New Ceremony Name',
        'menu_name'         => 'Ceremonies',
    );
    $args = array(
        'hierarchical'      => true, // hierarchical, like categories
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'ceremony'),
        'show_in_rest'      => true,
    );
    register_taxonomy('ceremonies', array('svadba'), $args);
});