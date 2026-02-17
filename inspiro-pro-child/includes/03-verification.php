<?php
/**
 * -----------------------------
 * 03 Verification
 * -----------------------------
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



// Handle email verification
function venture_handle_agent_email_verification() {

    if (
        isset($_GET['verify_account']) &&
        isset($_GET['uid']) &&
        isset($_GET['key'])
    ) {

        $user_id = intval($_GET['uid']);
        $key     = sanitize_text_field($_GET['key']);

        $saved_key = get_user_meta($user_id, 'venture_email_verification_key', true);

        if ($key && $saved_key && hash_equals($saved_key, $key)) {

            // Promote to full agent
            $user = new WP_User($user_id);
            $user->set_role('agent');

            // Remove verification key
            delete_user_meta($user_id, 'venture_email_verification_key');

            wp_die('Your email has been verified. Your account is now active.');

        } else {
            wp_die('Invalid or expired verification link.');
        }
    }

}
add_action('init', 'venture_handle_agent_email_verification');



// Prevent pending agents from logging in
function venture_block_pending_agent_login($user, $username, $password) {

    if (is_wp_error($user) || ! $user) {
        return $user;
    }

    if (in_array('pending_agent', (array) $user->roles, true)) {
        return new WP_Error(
            'venture_pending_verification',
            __('Please verify your email address before logging in.', 'venture')
        );
    }

    return $user;

}
add_filter('authenticate', 'venture_block_pending_agent_login', 30, 3);
