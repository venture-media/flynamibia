<?php
// =====================
// PHP includes
// =====================
$includes_dir = get_stylesheet_directory() . '/includes/';

if (is_dir($includes_dir)) {
    foreach (scandir($includes_dir) as $file) {
        $file_path = $includes_dir . $file;
        if (is_file($file_path) && strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'php') {
            require_once $file_path;
        }
    }
} else {
    error_log('Includes folder does not exist: ' . $includes_dir);
}

// =====================
// Parent CSS
// =====================

if ( !function_exists( 'chld_thm_cfg_parent_css' ) ):
    function chld_thm_cfg_parent_css() {
        wp_enqueue_style( 'chld_thm_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array() );
    }
endif;
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_parent_css', 10 );


// =====================
// Parent blocks (header and footer)
// =====================

add_action( 'after_setup_theme', function() {
    // Inherit parent block templates (header/footer)
    if ( function_exists( 'wp_get_theme' ) ) {
        $parent = wp_get_theme()->parent();
        if ( $parent ) {
            // This ensures the child sees the parent's block templates
            remove_theme_support( 'block-templates' ); // remove child (empty)
            add_theme_support( 'block-templates', $parent->get_stylesheet() );
        }
    }
}, 5 );
