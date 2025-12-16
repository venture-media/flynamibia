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
