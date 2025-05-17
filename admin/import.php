<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Admin Page for Import
function ld_course_grid_import_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Import SFWD Courses', 'ld-course-grid'); ?></h1>
        <?php if (!post_type_exists('sfwd-courses')): ?>
            <p style="color: red;"><?php _e('LearnDash courses (sfwd-courses) are not available. Please ensure LearnDash is installed and activated.', 'ld-course-grid'); ?></p>
        <?php else: ?>
            <p><?php _e('Import courses from LearnDash (sfwd-courses) into the custom ld_course post type.', 'ld-course-grid'); ?></p>
            <form id="ld-course-import-form">
                <p>
                    <label for="default_language"><?php _e('Default Language for Imported Courses', 'ld-course-grid'); ?></label><br>
                    <select id="default_language" name="default_language">
                        <option value="gr"><?php _e('Greek', 'ld-course-grid'); ?></option>
                        <option value="en"><?php _e('English', 'ld-course-grid'); ?></option>
                    </select>
                </p>
                <button type="button" id="ld-course-import-btn" class="button button-primary"><?php _e('Import Courses', 'ld-course-grid'); ?></button>
                <p><label><input type="checkbox" id="update_existing" name="update_existing"> <?php _e('Update existing imported courses', 'ld-course-grid'); ?></label></p>
            </form>
            <div id="ld-course-import-status"></div>
        <?php endif; ?>
    </div>
    <?php if (post_type_exists('sfwd-courses')): ?>
    <script>
        jQuery(document).ready(function($) {
            $('#ld-course-import-btn').on('click', function() {
                var button = $(this);
                var defaultLanguage = $('#default_language').val();
                var updateExisting = $('#update_existing').is(':checked');
                button.prop('disabled', true).text('<?php _e('Importing...', 'ld-course-grid'); ?>');
                $('#ld-course-import-status').html('<p><?php _e('Import in progress, please wait...', 'ld-course-grid'); ?></p>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'ld_course_grid_import_sfwd_courses',
                        nonce: '<?php echo wp_create_nonce('ld_course_grid_import_nonce'); ?>',
                        default_language: defaultLanguage,
                        update_existing: updateExisting
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#ld-course-import-status').html('<p>' + response.data.message + '</p>');
                        } else {
                            $('#ld-course-import-status').html('<p style="color: red;">' + response.data.message + '</p>');
                        }
                        button.prop('disabled', false).text('<?php _e('Import Courses', 'ld-course-grid'); ?>');
                    },
                    error: function(xhr, status, error) {
                        $('#ld-course-import-status').html('<p style="color: red;"><?php _e('An error occurred during the import: ', 'ld-course-grid'); ?>' + error + '</p>');
                        button.prop('disabled', false).text('<?php _e('Import Courses', 'ld-course-grid'); ?>');
                    }
                });
            });
        });
    </script>
    <?php endif; ?>
    <?php
}