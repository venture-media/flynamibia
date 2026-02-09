<?php
/**
 * -----------------------------
 * 00 Roles
 * -----------------------------
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register custom roles
 */
function venture_register_agent_role() {

    // Only add role if it doesn't already exist
    if ( ! get_role( 'agent' ) ) {

        add_role(
            'agent',
            __( 'Agent', 'venture' ),
            array(
                'read'                     => true,
                'edit_posts'               => false,
                'delete_posts'             => false,
                'edit_pages'               => false,
                'edit_others_posts'        => false,
                'publish_posts'            => false,
                'upload_files'             => true,  // can upload files (for profile images)
                'edit_user_meta'           => true,  // custom capability for agent meta
            )
        );
    }

}
add_action( 'init', 'venture_register_agent_role', 5 ); // early init, before shortcodes load


// Redirect non-logged-in users away from Agent Dashboard and all child pages
function protect_agent_dashboard_pages() {

    // Allow logged-in users
    if ( is_user_logged_in() ) {
        return;
    }

    // Only run on pages
    if ( ! is_page() ) {
        return;
    }

    global $post;

    $agent_parent_id = 13006;

    // If this page is the agent page OR a child of it
    if (
        $post->ID == $agent_parent_id ||
        in_array( $agent_parent_id, get_post_ancestors( $post->ID ), true )
    ) {
        wp_safe_redirect( wp_login_url( get_permalink( $agent_parent_id ) ) );
        exit;
    }
}
add_action( 'template_redirect', 'protect_agent_dashboard_pages' );



// Hide admin bar for agent users
add_action('after_setup_theme', function() {
    if (current_user_can('agent') && !current_user_can('administrator')) {
        show_admin_bar(false);
    }
});


// Redirect Agent users away from wp-admin to the agent page
function redirect_agent_from_admin() {
    if ( is_admin() && ! defined('DOING_AJAX') && current_user_can('agent') ) {
        wp_redirect( get_permalink(13006) ); // agent page ID
        exit;
    }
}
add_action( 'admin_init', 'redirect_agent_from_admin' );


// On login, redirect Agent to agent page
function agent_login_redirect( $redirect_to, $request, $user ) {
    if ( isset($user->roles) && is_array($user->roles) && in_array( 'agent', $user->roles ) ) {
        return get_permalink(13006); // agent page ID
    }
    return $redirect_to;
}
add_filter( 'login_redirect', 'agent_login_redirect', 10, 3 );
