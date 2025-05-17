<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add Meta Boxes
function ld_course_grid_add_meta_boxes() {
    add_meta_box(
        'ld_course_details',
        __('Course Details', 'ld-course-grid'),
        'ld_course_grid_meta_box_callback',
        'ld_course',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'ld_course_grid_add_meta_boxes');

// Meta Box Callback
function ld_course_grid_meta_box_callback($post) {
    wp_nonce_field('ld_course_grid_save_meta_box_data', 'ld_course_grid_meta_nonce');

    $price = get_post_meta($post->ID, '_ld_course_price', true);
    $course_url = get_post_meta($post->ID, '_ld_course_url', true);
    $button_text = get_post_meta($post->ID, '_ld_course_button_text', true);
    $created_by = get_post_meta($post->ID, '_ld_course_created_by', true);
    $lessons = get_post_meta($post->ID, '_ld_course_lessons', true);
    $quizzes = get_post_meta($post->ID, '_ld_course_quizzes', true);
    $source_course_id = get_post_meta($post->ID, '_ld_source_course_id', true);

    ?>
    <p>
        <label for="ld_course_price"><?php _e('Course Price (e.g., 49.99)', 'ld-course-grid'); ?></label><br>
        <input type="number" step="0.01" min="0" id="ld_course_price" name="ld_course_price" value="<?php echo esc_attr($price); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="ld_course_url"><?php _e('Course URL', 'ld-course-grid'); ?></label><br>
        <input type="url" id="ld_course_url" name="ld_course_url" value="<?php echo esc_attr($course_url); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="ld_course_button_text"><?php _e('Button Text', 'ld-course-grid'); ?></label><br>
        <input type="text" id="ld_course_button_text" name="ld_course_button_text" value="<?php echo esc_attr($button_text ? $button_text : 'Enroll Now'); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="ld_course_created_by"><?php _e('Created By', 'ld-course-grid'); ?></label><br>
        <select id="ld_course_created_by" name="ld_course_created_by" style="width: 100%;">
            <option value="human" <?php selected($created_by, 'human'); ?>>Human</option>
            <option value="ai" <?php selected($created_by, 'ai'); ?>>AI</option>
        </select>
    </p>
    <p>
        <label for="ld_course_lessons"><?php _e('Number of Lessons', 'ld-course-grid'); ?></label><br>
        <input type="number" min="0" id="ld_course_lessons" name="ld_course_lessons" value="<?php echo esc_attr($lessons ? $lessons : 0); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="ld_course_quizzes"><?php _e('Number of Quizzes', 'ld-course-grid'); ?></label><br>
        <input type="number" min="0" id="ld_course_quizzes" name="ld_course_quizzes" value="<?php echo esc_attr($quizzes ? $quizzes : 0); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="ld_source_course_id"><?php _e('Source LearnDash Course ID (if imported)', 'ld-course-grid'); ?></label><br>
        <input type="number" min="0" id="ld_source_course_id" name="ld_source_course_id" value="<?php echo esc_attr($source_course_id ? $source_course_id : ''); ?>" style="width: 100%;" readonly>
    </p>
    <?php
}

// Save Meta Box Data
function ld_course_grid_save_meta_box_data($post_id) {
    if (!isset($_POST['ld_course_grid_meta_nonce']) || !wp_verify_nonce($_POST['ld_course_grid_meta_nonce'], 'ld_course_grid_save_meta_box_data')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = array(
        'ld_course_price'       => function($value) { return is_numeric($value) && $value >= 0 ? floatval($value) : ''; },
        'ld_course_url'         => 'esc_url_raw',
        'ld_course_button_text' => 'sanitize_text_field',
        'ld_course_created_by'  => 'sanitize_text_field',
        'ld_course_lessons'     => 'absint',
        'ld_course_quizzes'     => 'absint',
    );

    foreach ($fields as $field => $sanitizer) {
        if (isset($_POST[$field])) {
            $value = call_user_func($sanitizer, $_POST[$field]);
            if ($value !== '') {
                update_post_meta($post_id, '_' . $field, $value);
            } else {
                delete_post_meta($post_id, '_' . $field);
            }
        } else {
            delete_post_meta($post_id, '_' . $field);
        }
    }
}
add_action('save_post_ld_course', 'ld_course_grid_save_meta_box_data');