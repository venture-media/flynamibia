<?php
/**
 * -----------------------------
 * 01 Shortcodes
 * -----------------------------
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


// Shortcode: [agent_dashboard]
function agent_dashboard_shortcode() {
    $user_id = get_current_user_id();

    // Only agents OR admins can see this page
    if ( ! ( current_user_can('agent') || current_user_can('administrator') ) ) {
        return '<p>You do not have permission to access this page.</p>';
    }


    // Handle form submission
    if ( isset($_POST['agent_nonce']) && wp_verify_nonce($_POST['agent_nonce'], 'agent_update') ) {
        if ( ! function_exists( 'media_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        // Save job title
        update_user_meta($user_id, 'agent_title', sanitize_text_field($_POST['agent_title']));

        // Save company
        update_user_meta($user_id, 'agent_company', sanitize_textarea_field($_POST['agent_company']));

        // Save country
        update_user_meta($user_id, 'agent_country', sanitize_textarea_field($_POST['agent_country']));

        // Save mobile, whatsapp and email
        update_user_meta($user_id, 'agent_mobile', sanitize_text_field($_POST['agent_mobile']));
        update_user_meta($user_id, 'agent_whatsapp', sanitize_text_field($_POST['agent_whatsapp']));
        update_user_meta($user_id, 'agent_email', sanitize_email($_POST['agent_email']));

        echo '<p class="agent-success">✅ Your details have been updated.</p>';
    }

    // Get existing values
    $title = get_user_meta($user_id, 'agent_title', true);
    $company   = get_user_meta($user_id, 'agent_company', true);
    $country   = get_user_meta($user_id, 'agent_country', true);
    $mobile = get_user_meta($user_id, 'agent_mobile', true);
    $whatsapp = get_user_meta($user_id, 'agent_whatsapp', true);
    $email  = get_user_meta($user_id, 'agent_email', true);

    ob_start(); ?>
    <form method="post" enctype="multipart/form-data" class="agent-profile-form">
        <label for="agent_title">Job Title</label>
        <input type="text" id="agent_title" name="agent_title" value="<?php echo esc_attr($title); ?>">

        <label for="agent_company">Company</label>
        <textarea id="agent_company" name="agent_company" rows="6"><?php echo esc_textarea($company); ?></textarea>

        <label for="agent_country">Country</label>
        <textarea id="agent_country" name="agent_country" rows="6"><?php echo esc_textarea($country); ?></textarea>
        
        <label for="agent_mobile">Mobile number</label>
        <input type="text" id="agent_mobile" name="agent_mobile" value="<?php echo esc_attr($mobile); ?>">

        <label for="agent_whatsapp">Whatsapp number</label>
        <input type="text" id="agent_whatsapp" name="agent_whatsapp" value="<?php echo esc_attr($whatsapp); ?>">
        
        <label for="agent_email">Email address</label>
        <input type="email" id="agent_email" name="agent_email" value="<?php echo esc_attr($email); ?>">

        <input type="hidden" name="agent_nonce" value="<?php echo wp_create_nonce('agent_update'); ?>">
        <button type="submit">Save Changes</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('agent_dashboard', 'agent_dashboard_shortcode');


// Shortcode: [agent_logout]
function agent_logout_shortcode() {
    if ( ! is_user_logged_in() ) {
        return ''; // hide for guests
    }

    $logout_url = wp_logout_url( home_url() ); // redirect to homepage after logout
    return '<a class="agent-profile-form-logout" href="' . esc_url( $logout_url ) . '">Log out</a>';
}
add_shortcode( 'agent_logout', 'agent_logout_shortcode' );


// Shortcode: [agent_name]
function agent_name_shortcode() {
    if ( ! is_user_logged_in() ) {
        return ''; // hide for guests
    }

    $user = wp_get_current_user();

    // Use display_name (can also use first_name or user_login if you prefer)
    return '<h2 class="agent-name">' . esc_html( $user->display_name ) . '</h2>';
}
add_shortcode('agent_name', 'agent_name_shortcode');


// Shortcode: [agent_directory]
function agent_directory_shortcode() {
    // Only show for logged-in agent or admins
  
    if ( ! ( current_user_can('agent') || current_user_can('administrator') ) ) {
        return '<p>You do not have permission to view this list.</p>';
    }

    $args = array(
        'role__in' => array('agent', 'administrator'),
        'number'   => 9999,
    );

    $users = get_users($args);

    if (empty($users)) {
        return '<p>No agent members found.</p>';
    }

    ob_start(); ?>

    <table class="agent-directory">
        <tbody>
            <?php foreach ($users as $user):
                $user_id = $user->ID;
                $title   = get_user_meta($user_id, 'agent_title', true);
                $company   = get_user_meta($user_id, 'agent_company', true);
                $country   = get_user_meta($user_id, 'agent_country', true);
                $mobile  = get_user_meta($user_id, 'agent_mobile', true);
                $whatsapp  = get_user_meta($user_id, 'agent_whatsapp', true);
                $email   = get_user_meta($user_id, 'agent_email', true);
                
                // skip empty users (no profile data)
                if (!$title && !$company && !$country && !$mobile && !$whatsapp && !$email) continue;
            ?>
                <tr>
                    <td class="agent-directory-name"><?php echo esc_html($user->display_name); ?></td>
                    <td class="agent-directory-title"><?php echo esc_html($title); ?></td>
                    <td class="agent-directory-company"><?php echo esc_html($company); ?></td>
                    <td class="agent-directory-country"><?php echo esc_html($country); ?></td>
                    <td class="agent-directory-mobile"><?php echo esc_html($mobile); ?></td>
                    <td class="agent-directory-whatsapp"><?php echo esc_html($whatsapp); ?></td>
                    <td class="agent-directory-email"><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <style>
        .agent-directory {
            border-collapse: collapse;
            width: 100%;
            border: none;
        }
        .agent-directory tr {
            border: solid 1px #AB292E);
        }
        .agent-directory td {
            padding: 10px;
            border: none;
            vertical-align: middle;
        }
    </style>

    <?php
    return ob_get_clean();
}
add_shortcode('agent_directory', 'agent_directory_shortcode');
