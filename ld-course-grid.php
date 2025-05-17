<?php
/*
Plugin Name: LearnDash Course Grid
Description: Creates a custom post type for LearnDash courses and displays them in a card grid with pagination and advanced filtering.
Version: 7.1.5
Author: Miaoulis n
License: GPL2
Text Domain: ld-course-grid
Domain Path: /languages
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load plugin translations
function ld_course_grid_load_textdomain() {
    load_plugin_textdomain('ld-course-grid', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'ld_course_grid_load_textdomain');

// Enqueue Styles and Scripts
function ld_course_grid_enqueue_assets() {
    wp_enqueue_style('ld-course-grid', plugins_url('assets/css/ld-course-grid.css', __FILE__), array(), '7.1.5');
    // Inline CSS to bypass dynamic-styles.php
    $settings = get_option('ld_course_grid_settings', [
        'card_background_color' => '#ffffff',
        'card_font_color' => '#333333',
        'card_title_color' => '#333333',
        'button_background_color' => '#0073aa',
        'button_text_color' => '#ffffff',
        'card_title_font' => 'Arial',
        'card_title_alignment' => 'left',
        'card_padding' => '15px',
        'card_content_spacing' => '5px',
        'card_alignment' => 'left',
    ]);
    $inline_css = ":root {
        --card-background-color: " . sanitize_hex_color($settings['card_background_color']) . ";
        --card-font-color: " . sanitize_hex_color($settings['card_font_color']) . ";
        --card-title-color: " . sanitize_hex_color($settings['card_title_color']) . ";
        --button-background-color: " . sanitize_hex_color($settings['button_background_color']) . ";
        --button-text-color: " . sanitize_hex_color($settings['button_text_color']) . ";
        --card-title-font: \"" . esc_attr($settings['card_title_font']) . "\", sans-serif;
        --card-title-alignment: " . (in_array($settings['card_title_alignment'], ['left', 'center', 'right']) ? $settings['card_title_alignment'] : 'left') . ";
        --card-padding: " . esc_attr($settings['card_padding']) . ";
        --card-content-spacing: " . esc_attr($settings['card_content_spacing']) . ";
        --card-alignment: " . (in_array($settings['card_alignment'], ['left', 'center', 'right']) ? $settings['card_alignment'] : 'left') . ";
    }";
    wp_add_inline_style('ld-course-grid', $inline_css);
    wp_enqueue_style('nouislider', 'https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.css', array(), '15.7.1');
    wp_enqueue_script('ld-course-grid', plugins_url('assets/js/ld-course-grid.js', __FILE__), array('jquery'), '7.1.5', true);
    wp_enqueue_script('nouislider', 'https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.7.1/nouislider.min.js', array(), '15.7.1', true);
    wp_localize_script('ld-course-grid', 'ldCourseGrid', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ld_course_grid_nonce'),
        'i18n'     => array(
            'loading'          => __('Loading...', 'ld-course-grid'),
            'error_loading'    => __('Error loading courses.', 'ld-course-grid'),
            'error_loading_with_message' => __('Error loading courses: ', 'ld-course-grid'),
            'prev_page'        => __('Previous', 'ld-course-grid'),
            'next_page'        => __('Next', 'ld-course-grid'),
            'price_range'      => __('Price Range: €%s - €%s', 'ld-course-grid'),
            'no_sfwd_courses'  => __('LearnDash courses (sfwd-courses) are not available. Please ensure LearnDash is installed and activated.', 'ld-course-grid'),
        ),
    ));
}
add_action('wp_enqueue_scripts', 'ld_course_grid_enqueue_assets');

// Enqueue Color Picker for Admin
function ld_course_grid_admin_enqueue_scripts($hook) {
    if ($hook === 'ld_course_page_ld_course_grid_settings') {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('ld-course-grid-admin', plugins_url('assets/js/ld-course-grid-admin.js', __FILE__), array('wp-color-picker', 'jquery'), '7.1.5', true);
    }
}
add_action('admin_enqueue_scripts', 'ld_course_grid_admin_enqueue_scripts');

// Include core functionality
require_once plugin_dir_path(__FILE__) . 'includes/post-types.php';
require_once plugin_dir_path(__FILE__) . 'includes/taxonomies.php';
require_once plugin_dir_path(__FILE__) . 'includes/meta-boxes.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax.php';
require_once plugin_dir_path(__FILE__) . 'includes/utilities.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';

// Include admin functionality
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/admin-menus.php';
    require_once plugin_dir_path(__FILE__) . 'admin/import.php';
}

// Flush rewrite rules on activation
function ld_course_grid_activate() {
    ld_course_grid_register_post_type();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'ld_course_grid_activate');

// Flush rewrite rules on deactivation
function ld_course_grid_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'ld_course_grid_deactivate');