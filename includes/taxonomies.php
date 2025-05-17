<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register Taxonomies
function ld_course_grid_register_taxonomies() {
    // Register Course Category Taxonomy
    $category_labels = array(
        'name'              => _x('Course Categories', 'Taxonomy General Name', 'ld-course-grid'),
        'singular_name'     => _x('Course Category', 'Taxonomy Singular Name', 'ld-course-grid'),
        'menu_name'         => __('Course Categories', 'ld-course-grid'),
    );

    $category_args = array(
        'hierarchical'      => true,
        'labels'            => $category_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'course-category'),
        'show_in_rest'      => true,
    );

    register_taxonomy('ld_course_category', array('ld_course'), $category_args);

    // Register Language Taxonomy
    $language_labels = array(
        'name'              => _x('Languages', 'Taxonomy General Name', 'ld-course-grid'),
        'singular_name'     => _x('Language', 'Taxonomy Singular Name', 'ld-course-grid'),
        'menu_name'         => __('Languages', 'ld-course-grid'),
    );

    $language_args = array(
        'hierarchical'      => false,
        'labels'            => $language_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'language'),
        'show_in_rest'      => true,
    );

    register_taxonomy('ld_language', array('ld_course'), $language_args);

    // Ensure default language terms exist
    $languages = array(
        'en' => 'English',
        'gr' => 'Greek',
    );
    foreach ($languages as $slug => $name) {
        if (!term_exists($slug, 'ld_language')) {
            wp_insert_term($name, 'ld_language', array('slug' => $slug));
        }
    }
}
add_action('init', 'ld_course_grid_register_taxonomies');