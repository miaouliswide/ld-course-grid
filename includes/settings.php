<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register Settings
function ld_course_grid_register_settings() {
    register_setting('ld_course_grid_settings_group', 'ld_course_grid_settings', array(
        'sanitize_callback' => 'ld_course_grid_sanitize_settings',
    ));

    add_settings_section(
        'ld_course_grid_style_section',
        __('Card Style Settings', 'ld-course-grid'),
        null,
        'ld_course_grid_settings'
    );

    add_settings_field(
        'card_background_color',
        __('Card Background Color', 'ld-course-grid'),
        'ld_course_grid_card_background_color_callback',
        'ld_course_grid_settings',
        'ld_course_grid_style_section'
    );

    add_settings_field(
        'card_font_color',
        __('Card Font Color', 'ld-course-grid'),
        'ld_course_grid_card_font_color_callback',
        'ld_course_grid_settings',
        'ld_course_grid_style_section'
    );

    add_settings_field(
        'card_title_color',
        __('Card Title Color', 'ld-course-grid'),
        'ld_course_grid_card_title_color_callback',
        'ld_course_grid_settings',
        'ld_course_grid_style_section'
    );

    add_settings_field(
        'button_background_color',
        __('Button Background Color', 'ld-course-grid'),
        'ld_course_grid_button_background_color_callback',
        'ld_course_grid_settings',
        'ld_course_grid_style_section'
    );

    add_settings_field(
        'button_text_color',
        __('Button Text Color', 'ld-course-grid'),
        'ld_course_grid_button_text_color_callback',
        'ld_course_grid_settings',
        'ld_course_grid_style_section'
    );

    add_settings_field(
        'card_title_font',
        __('Card Title Font', 'ld-course-grid'),
        'ld_course_grid_card_title_font_callback',
        'ld_course_grid_settings',
        'ld_course_grid_style_section'
    );

    add_settings_field(
        'card_title_alignment',
        __('Card Title Alignment', 'ld-course-grid'),
        'ld_course_grid_card_title_alignment_callback',
        'ld_course_grid_settings',
        'ld_course_grid_style_section'
    );

    add_settings_field(
        'card_padding',
        __('Card Padding', 'ld-course-grid'),
        'ld_course_grid_card_padding_callback',
        'ld_course_grid_settings',
        'ld_course_grid_style_section'
    );

    add_settings_field(
        'card_content_spacing',
        __('Card Content Spacing', 'ld-course-grid'),
        'ld_course_grid_card_content_spacing_callback',
        'ld_course_grid_settings',
        'ld_course_grid_style_section'
    );

    add_settings_field(
        'card_alignment',
        __('Card Content Alignment', 'ld-course-grid'),
        'ld_course_grid_card_alignment_callback',
        'ld_course_grid_settings',
        'ld_course_grid_style_section'
    );
}
add_action('admin_init', 'ld_course_grid_register_settings');

// Sanitize Settings
function ld_course_grid_sanitize_settings($input) {
    $sanitized = array();

    $sanitized['card_background_color'] = sanitize_hex_color($input['card_background_color'] ?? '#ffffff');
    $sanitized['card_font_color'] = sanitize_hex_color($input['card_font_color'] ?? '#333333');
    $sanitized['card_title_color'] = sanitize_hex_color($input['card_title_color'] ?? '#333333');
    $sanitized['button_background_color'] = sanitize_hex_color($input['button_background_color'] ?? '#0073aa');
    $sanitized['button_text_color'] = sanitize_hex_color($input['button_text_color'] ?? '#ffffff');
    $sanitized['card_title_font'] = sanitize_text_field($input['card_title_font'] ?? 'Arial');
    $sanitized['card_title_alignment'] = in_array($input['card_title_alignment'] ?? 'left', ['left', 'center', 'right']) ? $input['card_title_alignment'] : 'left';
    $sanitized['card_padding'] = sanitize_text_field($input['card_padding'] ?? '15px');
    $sanitized['card_content_spacing'] = sanitize_text_field($input['card_content_spacing'] ?? '5px');
    $sanitized['card_alignment'] = in_array($input['card_alignment'] ?? 'left', ['left', 'center', 'right']) ? $input['card_alignment'] : 'left';

    return $sanitized;
}

// Settings Field Callbacks
function ld_course_grid_card_background_color_callback() {
    $settings = get_option('ld_course_grid_settings', []);
    $value = $settings['card_background_color'] ?? '#ffffff';
    ?>
    <input type="text" name="ld_course_grid_settings[card_background_color]" value="<?php echo esc_attr($value); ?>" class="ld-course-grid-color-picker" data-default-color="#ffffff">
    <?php
}

function ld_course_grid_card_font_color_callback() {
    $settings = get_option('ld_course_grid_settings', []);
    $value = $settings['card_font_color'] ?? '#333333';
    ?>
    <input type="text" name="ld_course_grid_settings[card_font_color]" value="<?php echo esc_attr($value); ?>" class="ld-course-grid-color-picker" data-default-color="#333333">
    <?php
}

function ld_course_grid_card_title_color_callback() {
    $settings = get_option('ld_course_grid_settings', []);
    $value = $settings['card_title_color'] ?? '#333333';
    ?>
    <input type="text" name="ld_course_grid_settings[card_title_color]" value="<?php echo esc_attr($value); ?>" class="ld-course-grid-color-picker" data-default-color="#333333">
    <?php
}

function ld_course_grid_button_background_color_callback() {
    $settings = get_option('ld_course_grid_settings', []);
    $value = $settings['button_background_color'] ?? '#0073aa';
    ?>
    <input type="text" name="ld_course_grid_settings[button_background_color]" value="<?php echo esc_attr($value); ?>" class="ld-course-grid-color-picker" data-default-color="#0073aa">
    <?php
}

function ld_course_grid_button_text_color_callback() {
    $settings = get_option('ld_course_grid_settings', []);
    $value = $settings['button_text_color'] ?? '#ffffff';
    ?>
    <input type="text" name="ld_course_grid_settings[button_text_color]" value="<?php echo esc_attr($value); ?>" class="ld-course-grid-color-picker" data-default-color="#ffffff">
    <?php
}

function ld_course_grid_card_title_font_callback() {
    $settings = get_option('ld_course_grid_settings', []);
    $value = $settings['card_title_font'] ?? 'Arial';
    $fonts = ['Arial', 'Helvetica', 'Times New Roman', 'Georgia', 'Verdana', 'Trebuchet MS', 'Courier New'];
    ?>
    <select name="ld_course_grid_settings[card_title_font]">
        <?php foreach ($fonts as $font): ?>
            <option value="<?php echo esc_attr($font); ?>" <?php selected($value, $font); ?>><?php echo esc_html($font); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}

function ld_course_grid_card_title_alignment_callback() {
    $settings = get_option('ld_course_grid_settings', []);
    $value = $settings['card_title_alignment'] ?? 'left';
    ?>
    <select name="ld_course_grid_settings[card_title_alignment]">
        <option value="left" <?php selected($value, 'left'); ?>><?php _e('Left', 'ld-course-grid'); ?></option>
        <option value="center" <?php selected($value, 'center'); ?>><?php _e('Center', 'ld-course-grid'); ?></option>
        <option value="right" <?php selected($value, 'right'); ?>><?php _e('Right', 'ld-course-grid'); ?></option>
    </select>
    <?php
}

function ld_course_grid_card_padding_callback() {
    $settings = get_option('ld_course_grid_settings', []);
    $value = $settings['card_padding'] ?? '15px';
    ?>
    <input type="text" name="ld_course_grid_settings[card_padding]" value="<?php echo esc_attr($value); ?>" placeholder="e.g., 15px">
    <p class="description"><?php _e('Enter a valid CSS padding value (e.g., 15px, 1rem).', 'ld-course-grid'); ?></p>
    <?php
}

function ld_course_grid_card_content_spacing_callback() {
    $settings = get_option('ld_course_grid_settings', []);
    $value = $settings['card_content_spacing'] ?? '5px';
    ?>
    <input type="text" name="ld_course_grid_settings[card_content_spacing]" value="<?php echo esc_attr($value); ?>" placeholder="e.g., 5px">
    <p class="description"><?php _e('Enter a valid CSS margin value for spacing between title, category, and language (e.g., 5px, 0.5rem).', 'ld-course-grid'); ?></p>
    <?php
}

function ld_course_grid_card_alignment_callback() {
    $settings = get_option('ld_course_grid_settings', []);
    $value = $settings['card_alignment'] ?? 'left';
    ?>
    <select name="ld_course_grid_settings[card_alignment]">
        <option value="left" <?php selected($value, 'left'); ?>><?php _e('Left', 'ld-course-grid'); ?></option>
        <option value="center" <?php selected($value, 'center'); ?>><?php _e('Center', 'ld-course-grid'); ?></option>
        <option value="right" <?php selected($value, 'right'); ?>><?php _e('Right', 'ld-course-grid'); ?></option>
    </select>
    <?php
}

// Add Settings Menu
function ld_course_grid_settings_menu() {
    add_submenu_page(
        'edit.php?post_type=ld_course',
        __('Course Grid Settings', 'ld-course-grid'),
        __('Settings', 'ld-course-grid'),
        'manage_options',
        'ld_course_grid_settings',
        'ld_course_grid_settings_page'
    );
}
add_action('admin_menu', 'ld_course_grid_settings_menu');

// Settings Page
function ld_course_grid_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('LearnDash Course Grid Settings', 'ld-course-grid'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('ld_course_grid_settings_group');
            do_settings_sections('ld_course_grid_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}