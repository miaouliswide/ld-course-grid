<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type
function ld_course_grid_register_post_type() {
    $labels = array(
        'name'               => _x('LearnDash Courses', 'Post Type General Name', 'ld-course-grid'),
        'singular_name'      => _x('LearnDash Course', 'Post Type Singular Name', 'ld-course-grid'),
        'menu_name'          => __('LearnDash Courses', 'ld-course-grid'),
        'name_admin_bar'     => __('LearnDash Course', 'ld-course-grid'),
        'add_new'            => __('Add New', 'ld-course-grid'),
        'add_new_item'       => __('Add New Course', 'ld-course-grid'),
        'edit_item'          => __('Edit Course', 'ld-course-grid'),
        'new_item'           => __('New Course', 'ld-course-grid'),
        'view_item'          => __('View Course', 'ld-course-grid'),
        'all_items'          => __('All Courses', 'ld-course-grid'),
        'search_items'       => __('Search Courses', 'ld-course-grid'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'supports'           => array('title', 'thumbnail', 'excerpt'),
        'taxonomies'         => array('ld_course_category', 'post_tag', 'ld_language'),
        'menu_icon'          => 'dashicons-book-alt',
        'show_in_rest'       => true,
    );

    register_post_type('ld_course', $args);
}
add_action('init', 'ld_course_grid_register_post_type');