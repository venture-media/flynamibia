<?php
/**
 * -----------------------------
 * 04 Custom Post Types
 * -----------------------------
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


// Campaigns
function cpt_campaign() {
    register_post_type( 'campaign', [
        'labels' => [
            'name' => 'Campaign',
            'singular_name' => 'Campaign',
            'add_new' => 'Add Campaign',
            'add_new_item' => 'Add New Campaign',
            'edit_item' => 'Edit Campaign',
            'new_item' => 'New Campaign',
            'view_item' => 'View Campaign',
            'search_items' => 'Search Campaigns',
            'not_found' => 'No Campaigns found',
            'not_found_in_trash' => 'No Campaigns found in Trash',
            'all_items' => 'All Campaigns',
            'menu_name' => 'Campaigns',
            'name_admin_bar' => 'Campaign'
        ],
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-megaphone',
        'supports' => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ]
    ]);
}
add_action( 'init', 'cpt_campaign', 0 );

function cpt_campaign_taxonomy() {
    $labels = [
        'name' => 'Campaign Categories',
        'singular_name' => 'Campaign Category',
        'search_items' => 'Search Categories',
        'all_items' => 'All Categories',
        'parent_item' => 'Parent Category',
        'parent_item_colon' => 'Parent Category:',
        'edit_item' => 'Edit Category',
        'update_item' => 'Update Category',
        'add_new_item' => 'Add New Category',
        'new_item_name' => 'New Category Name',
        'menu_name' => 'Campaign Categories',
    ];

    register_taxonomy( 'campaign_category', [ 'campaign' ], [
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
    ]);
}
add_action( 'init', 'cpt_campaign_taxonomy', 10 );
