<?php
/**
 * -----------------------------
 * 03 Robots
 * -----------------------------
 */

// Pages with noindex tag
add_action('wp_head', function() {
    if (is_page([13067, 13059])) { 
        echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
    }
});
