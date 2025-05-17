<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add Admin Menu for Importing
function ld_course_grid_admin_menu() {
    if (post_type_exists('sfwd-courses')) {
        add_submenu_page(
            'edit.php?post_type=ld_course',
            __('Import SFWD Courses', 'ld-course-grid'),
            __('Import SFWD Courses', 'ld-course-grid'),
            'manage_options',
            'ld_course_grid_import',
            'ld_course_grid_import_page'
        );
    }
}
add_action('admin_menu', 'ld_course_grid_admin_menu');