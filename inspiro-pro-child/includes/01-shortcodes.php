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
    if (
        isset($_POST['agent_nonce']) &&
        wp_verify_nonce($_POST['agent_nonce'], 'agent_update')
    ) {
    
        $user_id = get_current_user_id();
    
        // Save fields safely
        update_user_meta($user_id, 'agent_title', isset($_POST['agent_title']) ? sanitize_text_field($_POST['agent_title']) : '');
        update_user_meta($user_id, 'agent_company', isset($_POST['agent_company']) ? sanitize_textarea_field($_POST['agent_company']) : '');
        update_user_meta($user_id, 'agent_country', isset($_POST['agent_country']) ? sanitize_textarea_field($_POST['agent_country']) : '');
        update_user_meta($user_id, 'agent_mobile', isset($_POST['agent_mobile']) ? sanitize_text_field($_POST['agent_mobile']) : '');
        update_user_meta($user_id, 'agent_whatsapp', isset($_POST['agent_whatsapp']) ? sanitize_text_field($_POST['agent_whatsapp']) : '');
        update_user_meta($user_id, 'agent_email', isset($_POST['agent_email']) ? sanitize_email($_POST['agent_email']) : '');
    
        // Set a transient to show a one-time success message
        set_transient('agent_profile_updated_' . $user_id, true, 30);
    
        // Redirect to the same page (PRG pattern)
        wp_safe_redirect(add_query_arg('updated', 'true', get_permalink()));
        exit;
    }
    
    // Success message
    $success_message = '';
    $user_id = get_current_user_id();
    if ( get_transient('agent_profile_updated_' . $user_id) ) {
        $success_message = '<p class="agent-success">✅ Your details have been updated.</p>';
        delete_transient('agent_profile_updated_' . $user_id);
    }


    // Get existing values
    $title = get_user_meta($user_id, 'agent_title', true);
    $company   = get_user_meta($user_id, 'agent_company', true);
    $country   = get_user_meta($user_id, 'agent_country', true);
    $mobile = get_user_meta($user_id, 'agent_mobile', true);
    $whatsapp = get_user_meta($user_id, 'agent_whatsapp', true);
    $email  = get_user_meta($user_id, 'agent_email', true);

    ob_start();
    echo $success_message;
    ?>
    <form method="post" enctype="multipart/form-data" class="agent-profile-form">
        <label for="agent_title">Job Title</label>
        <input type="text" id="agent_title" name="agent_title" value="<?php echo esc_attr($title); ?>">

        <label for="agent_company">Company</label>
        <input type="text" id="agent_company" name="agent_company" value="<?php echo esc_attr($company); ?>">

        <label for="agent_country">Country</label>
        <input type="text" id="agent_country" name="agent_country" value="<?php echo esc_attr($country); ?>">
        
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

    if ( ! current_user_can('administrator') ) {
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
        <thead>
            <tr>
                <th>Name</th>
                <th>Job Title</th>
                <th>Company</th>
                <th>Country</th>
                <th>Mobile</th>
                <th>WhatsApp</th>
                <th>Email</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($users as $user):

                $user_id  = $user->ID;
                $title    = get_user_meta($user_id, 'agent_title', true);
                $company  = get_user_meta($user_id, 'agent_company', true);
                $country  = get_user_meta($user_id, 'agent_country', true);
                $mobile   = get_user_meta($user_id, 'agent_mobile', true);
                $whatsapp = get_user_meta($user_id, 'agent_whatsapp', true);
                $email    = get_user_meta($user_id, 'agent_email', true);

                // Skip users with no profile data
                if (!$title && !$company && !$country && !$mobile && !$whatsapp && !$email) {
                    continue;
                }
            ?>
                <tr>
                    <td class="agent-directory-name"><?php echo esc_html($user->display_name); ?></td>
                    <td class="agent-directory-title"><?php echo esc_html($title); ?></td>
                    <td class="agent-directory-company"><?php echo esc_html($company); ?></td>
                    <td class="agent-directory-country"><?php echo esc_html($country); ?></td>
                    <td class="agent-directory-mobile"><?php echo esc_html($mobile); ?></td>
                    <td class="agent-directory-whatsapp"><?php echo esc_html($whatsapp); ?></td>
                    <td class="agent-directory-email">
                        <a href="mailto:<?php echo esc_attr($email); ?>">
                            <?php echo esc_html($email); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    return ob_get_clean();
}
add_shortcode('agent_directory', 'agent_directory_shortcode');


function agent_register_shortcode() {

    if ( is_user_logged_in() ) {
        return '<p>You are already logged in.</p>';
    }

    $errors = [];
    $success = false;

    if (
        isset($_POST['agent_register_nonce']) &&
        wp_verify_nonce($_POST['agent_register_nonce'], 'agent_register')
    ) {
    
        // Honeypot – fail fast
        if ( ! empty($_POST['website']) ) {
            wp_safe_redirect( home_url() );
            exit;
        }
    
        $username = sanitize_user( trim($_POST['username'] ?? '') );
        $email    = sanitize_email( trim($_POST['email'] ?? '') );
        $password = trim($_POST['password'] ?? '');
    
        if ( empty($username) || empty($email) || empty($password) ) {
            $errors[] = 'All fields are required.';
        }
    
        if ( username_exists($username) ) {
            $errors[] = 'Username already exists.';
        }
    
        if ( email_exists($email) ) {
            $errors[] = 'Email already registered.';
        }

        
        if ( empty($errors) ) {
            $user_id = wp_create_user($username, $password, $email);

            if ( ! is_wp_error($user_id) ) {

                // Assign agent role
                $user = new WP_User($user_id);
                $user->set_role('agent');

                // Auto login
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);

                // Redirect to agent dashboard
                wp_safe_redirect( get_permalink(13006) );
                exit;
            } else {
                $errors[] = $user_id->get_error_message();
            }
        }
    }

    ob_start();
    ?>

    <?php if ($errors): ?>
        <ul class="agent-register-errors">
            <?php foreach ($errors as $error): ?>
                <li><?php echo esc_html($error); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post" class="agent-register-form">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <input type="hidden" name="agent_register_nonce"
               value="<?php echo wp_create_nonce('agent_register'); ?>">

        <!-- Honeypot field (should stay empty) -->
        <div style="display:none;">
            <label>Leave this field empty</label>
            <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <button type="submit">Register</button>
    </form>

    <?php
    return ob_get_clean();
}
add_shortcode('agent_register', 'agent_register_shortcode');
