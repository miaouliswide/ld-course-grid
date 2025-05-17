<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// AJAX Handler for Pagination
function ld_course_grid_pagination() {
    check_ajax_referer('ld_course_grid_nonce', 'nonce');

    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $tag = isset($_POST['tag']) ? sanitize_text_field($_POST['tag']) : '';
    $language = isset($_POST['language']) ? sanitize_text_field($_POST['language']) : '';
    $price_min = isset($_POST['price_min']) && $_POST['price_min'] !== '' ? floatval($_POST['price_min']) : '';
    $price_max = isset($_POST['price_max']) && $_POST['price_max'] !== '' ? floatval($_POST['price_max']) : '';
    $search_term = isset($_POST['s']) ? sanitize_text_field($_POST['s']) : '';

    // Validate inputs
    if ($price_min !== '' && $price_min < 0) {
        $price_min = 0;
    }
    if ($price_max !== '' && $price_max < 0) {
        $price_max = '';
    }
    if ($price_min !== '' && $price_max !== '' && $price_min > $price_max) {
        $temp = $price_min;
        $price_min = $price_max;
        $price_max = $temp;
    }

    // Calculate total posts for pagination
    $total_args = array(
        'post_type'      => 'ld_course',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    );

    // Apply search
    if (!empty($search_term)) {
        $total_args['s'] = $search_term;
        add_filter('posts_where', function($where) use ($search_term) {
            global $wpdb;
            $search_term = esc_sql($search_term);
            $where .= " OR EXISTS (
                SELECT 1 FROM {$wpdb->term_relationships} tr
                JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                WHERE tr.object_id = {$wpdb->posts}.ID
                AND tt.taxonomy = 'ld_course_category'
                AND t.name LIKE '%$search_term%'
            )";
            return $where;
        });
    }

    // Apply filters to total query
    $tax_query = array('relation' => 'AND');
    if ($category && term_exists($category, 'ld_course_category')) {
        $tax_query[] = array(
            'taxonomy' => 'ld_course_category',
            'field'    => 'slug',
            'terms'    => $category,
        );
    }
    if ($tag && term_exists($tag, 'post_tag')) {
        $tax_query[] = array(
            'taxonomy' => 'post_tag',
            'field'    => 'slug',
            'terms'    => $tag,
        );
    }
    if ($language && term_exists($language, 'ld_language')) {
        $tax_query[] = array(
            'taxonomy' => 'ld_language',
            'field'    => 'slug',
            'terms'    => $language,
        );
    }
    if (count($tax_query) > 1) {
        $total_args['tax_query'] = $tax_query;
    }

    $meta_query = array('relation' => 'AND');
    if ($price_min !== '') {
        $meta_query[] = array(
            'key'     => '_ld_course_price',
            'value'   => $price_min,
            'compare' => '>=',
            'type'    => 'DECIMAL(10,2)',
        );
    }
    if ($price_max !== '') {
        $meta_query[] = array(
            'key'     => '_ld_course_price',
            'value'   => $price_max,
            'compare' => '<=',
            'type'    => 'DECIMAL(10,2)',
        );
    }
    if ($price_min === 0) {
        $meta_query[] = array(
            'relation' => 'OR',
            array(
                'key'     => '_ld_course_price',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key'     => '_ld_course_price',
                'value'   => 0,
                'compare' => '=',
                'type'    => 'DECIMAL(10,2)',
            ),
        );
    }
    if (count($meta_query) > 1 || (count($meta_query) == 1 && $price_min === 0)) {
        $total_args['meta_query'] = $meta_query;
    }

    $total_query = new WP_Query($total_args);
    $total_posts = count($total_query->posts);
    $posts_per_page = 3;
    $max_pages = max(1, ceil($total_posts / $posts_per_page));

    // Remove the filter to avoid affecting other queries
    if (!empty($search_term)) {
        remove_filter('posts_where', function() {});
    }

    // Fetch posts for the current page
    $args = array(
        'post_type'      => 'ld_course',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged'          => $page,
    );

    // Clear any global query vars that might interfere
    $temp = $wp_query;
    $wp_query = null;
    wp_reset_query();

    // Apply search
    if (!empty($search_term)) {
        $args['s'] = $search_term;
        add_filter('posts_where', function($where) use ($search_term) {
            global $wpdb;
            $search_term = esc_sql($search_term);
            $where .= " OR EXISTS (
                SELECT 1 FROM {$wpdb->term_relationships} tr
                JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                WHERE tr.object_id = {$wpdb->posts}.ID
                AND tt.taxonomy = 'ld_course_category'
                AND t.name LIKE '%$search_term%'
            )";
            return $where;
        });
    }

    if (isset($total_args['tax_query'])) {
        $args['tax_query'] = $total_args['tax_query'];
    }
    if (isset($total_args['meta_query'])) {
        $args['meta_query'] = $total_args['meta_query'];
    }

    $query = new WP_Query($args);

    // Generate pagination HTML
    $pagination = ld_course_grid_custom_pagination($page, $max_pages);

    ob_start();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $course_id = get_the_ID();
            $price = get_post_meta($course_id, '_ld_course_price', true);
            $created_by = get_post_meta($course_id, '_ld_course_created_by', true);
            $lessons = get_post_meta($course_id, '_ld_course_lessons', true);
            $quizzes = get_post_meta($course_id, '_ld_course_quizzes', true);
            $categories = get_the_terms($course_id, 'ld_course_category');
            $category_names = !empty($categories) && !is_wp_error($categories) ? wp_list_pluck($categories, 'name') : [__('Uncategorized', 'ld-course-grid')];
            $category_display = esc_html(implode(', ', $category_names));
            $languages = get_the_terms($course_id, 'ld_language');
            $language_name = !empty($languages) && !is_wp_error($languages) ? esc_html($languages[0]->name) : __('Unknown', 'ld-course-grid');

            ?>
            <div class="ld-course-card" data-course-id="<?php echo esc_attr($course_id); ?>">
                <?php if (has_post_thumbnail()): ?>
                    <div class="ld-course-image">
                        <?php the_post_thumbnail('medium'); ?>
                    </div>
                <?php endif; ?>
                <div class="ld-course-content">
                    <h3><?php the_title(); ?></h3>
                    <p class="ld-course-category"><?php echo $category_display; ?></p>
                    <p class="ld-course-language"><?php echo $language_name; ?></p>
                    <div class="ld-course-stats">
                        <span class="ld-course-lessons">📚 <?php echo esc_html($lessons ?: 0); ?></span>
                        <span class="ld-course-quizzes">🧩 <?php echo esc_html($quizzes ?: 0); ?></span>
                    </div>
                    <div class="ld-course-price-created">
                        <span class="ld-course-price"><?php echo $price !== '' ? esc_html(number_format((float)$price, 2)) . ' €' : 'Free'; ?></span>
                        <span class="ld-course-created-by"><?php echo esc_html(ucfirst($created_by ?: 'human')); ?> Created</span>
                    </div>
                    <a href="<?php echo esc_url(get_post_meta($course_id, '_ld_course_url', true) ?: '#'); ?>" class="ld-course-button">
                        <?php echo esc_html(get_post_meta($course_id, '_ld_course_button_text', true) ?: 'Enroll Now'); ?>
                    </a>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<p>' . __('No courses found for the selected search or filters. Try different options.', 'ld-course-grid') . '</p>';
    }

    $html = ob_get_clean();
    // Remove the filter to avoid affecting other queries
    if (!empty($search_term)) {
        remove_filter('posts_where', function() {});
    }
    // Restore global query
    $wp_query = $temp;
    wp_reset_postdata();

    wp_send_json_success(array(
        'html'      => $html,
        'pagination' => $pagination,
        'max_pages' => $max_pages,
        'page'      => $page,
        'total_posts' => $total_posts,
        'filters'   => array(
            'course_category'   => $category,
            'tag'        => $tag,
            'language'   => $language,
            'price_min'  => $price_min,
            'price_max'  => $price_max,
            's'          => $search_term,
        ),
    ));
}
add_action('wp_ajax_ld_course_grid_pagination', 'ld_course_grid_pagination');
add_action('wp_ajax_nopriv_ld_course_grid_pagination', 'ld_course_grid_pagination');

// AJAX Handler for Importing SFWD Courses
function ld_course_grid_import_sfwd_courses() {
    check_ajax_referer('ld_course_grid_import_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'ld-course-grid')));
    }

    $default_language = isset($_POST['default_language']) ? sanitize_text_field($_POST['default_language']) : 'gr';
    $update_existing = isset($_POST['update_existing']) && $_POST['update_existing'] === 'on';

    // Verify LearnDash is active
    if (!post_type_exists('sfwd-courses')) {
        wp_send_json_error(array('message' => __('LearnDash is not active or sfwd-courses post type is missing.', 'ld-course-grid')));
    }

    // Query all sfwd-courses for import
    $args = array(
        'post_type'      => 'sfwd-courses',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);
    $imported = 0;
    $updated = 0;
    $skipped = 0;
    $errors = array();
    $uncategorized_term = get_term_by('slug', 'uncategorized', 'category');

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $sfwd_course_id = get_the_ID();
            $title = get_the_title();

            // Check if course was already imported
            $existing_post = get_posts(array(
                'post_type'      => 'ld_course',
                'post_status'    => 'publish',
                'meta_query'     => array(
                    array(
                        'key'   => '_ld_source_course_id',
                        'value' => $sfwd_course_id,
                    ),
                ),
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ));

            // Get course details
            $course_meta = get_post_meta($sfwd_course_id);
            $price = isset($course_meta['course_price'][0]) ? floatval($course_meta['course_price'][0]) : 0;
            $price_type = isset($course_meta['course_price_type'][0]) ? sanitize_text_field($course_meta['course_price_type'][0]) : 'free';
            $tags = wp_get_post_tags($sfwd_course_id, array('fields' => 'ids'));
            $course_url = get_permalink($sfwd_course_id);
            $button_text = isset($course_meta['custom_button_label'][0]) ? sanitize_text_field($course_meta['custom_button_label'][0]) : 'Enroll Now';
            $created_by = isset($course_meta['_ld_course_created_by'][0]) ? sanitize_text_field($course_meta['_ld_course_created_by'][0]) : 'human';

            // Get lessons and quizzes
            $lessons = 0;
            $quizzes = 0;

            if (function_exists('learndash_get_course_steps')) {
                $steps = learndash_get_course_steps($sfwd_course_id);
                foreach ($steps as $step_id) {
                    $step_type = get_post_type($step_id);
                    if ($step_type === 'sfwd-lessons') {
                        $lessons++;
                        $lesson_quizzes = function_exists('learndash_get_lesson_quiz_list') ? learndash_get_lesson_quiz_list($step_id) : array();
                        if (!empty($lesson_quizzes)) {
                            $quizzes += count($lesson_quizzes);
                        }
                    } elseif ($step_type === 'sfwd-quiz') {
                        $quizzes++;
                    } elseif ($step_type === 'sfwd-topic') {
                        $topic_quizzes = function_exists('learndash_get_lesson_quiz_list') ? learndash_get_lesson_quiz_list($step_id) : array();
                        if (!empty($topic_quizzes)) {
                            $quizzes += count($topic_quizzes);
                        }
                    }
                }
            } else {
                // Fallback: Parse ld_course_steps meta
                $steps_meta = maybe_unserialize(get_post_meta($sfwd_course_id, 'ld_course_steps', true));
                if (is_array($steps_meta) && isset($steps_meta['steps']['h'])) {
                    foreach ($steps_meta['steps']['h'] as $step_type => $step_items) {
                        if ($step_type === 'sfwd-lessons') {
                            $lessons += count($step_items);
                            foreach ($step_items as $lesson_id => $lesson_data) {
                                $lesson_quizzes = maybe_unserialize(get_post_meta($lesson_id, '_sfwd-quizzes', true));
                                if (is_array($lesson_quizzes)) {
                                    $quizzes += count($lesson_quizzes);
                                }
                            }
                        } elseif ($step_type === 'sfwd-quiz') {
                            $quizzes += count($step_items);
                        } elseif ($step_type === 'sfwd-topic') {
                            foreach ($step_items as $topic_id => $topic_data) {
                                $topic_quizzes = maybe_unserialize(get_post_meta($topic_id, '_sfwd-quizzes', true));
                                if (is_array($topic_quizzes)) {
                                    $quizzes += count($topic_quizzes);
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($existing_post) && !$update_existing) {
                $skipped++;
                $errors[] = sprintf(__('Skipped course "%s" (already imported).', 'ld-course-grid'), $title);
                continue;
            }

            // Create or update ld_course post
            $new_post = array(
                'post_title'    => $title,
                'post_type'     => 'ld_course',
                'post_status'   => 'publish',
                'post_excerpt'  => get_the_excerpt(),
            );

            if (empty($existing_post)) {
                $new_post_id = wp_insert_post($new_post, true);
                if (is_wp_error($new_post_id)) {
                    $skipped++;
                    $errors[] = sprintf(__('Failed to import course "%s": %s', 'ld-course-grid'), $title, $new_post_id->get_error_message());
                    continue;
                }
                $imported++;
            } else {
                $new_post_id = $existing_post[0];
                $new_post['ID'] = $new_post_id;
                wp_update_post($new_post);
                $updated++;
            }

            // Save meta fields
            update_post_meta($new_post_id, '_ld_course_price', $price);
            update_post_meta($new_post_id, '_ld_course_url', esc_url_raw($course_url));
            update_post_meta($new_post_id, '_ld_course_button_text', $button_text);
            update_post_meta($new_post_id, '_ld_course_created_by', $created_by);
            update_post_meta($new_post_id, '_ld_course_lessons', absint($lessons));
            update_post_meta($new_post_id, '_ld_course_quizzes', absint($quizzes));
            update_post_meta($new_post_id, '_ld_source_course_id', absint($sfwd_course_id));

            // Get sfwd-courses categories from multiple potential taxonomies
            $taxonomies = array('category', 'ld_course_category');
            $sfwd_course_categories = wp_get_object_terms($sfwd_course_id, $taxonomies, array('fields' => 'all'));
            $categories = array();
            if (!empty($sfwd_course_categories) && !is_wp_error($sfwd_course_categories)) {
                foreach ($sfwd_course_categories as $cat) {
                    if ($uncategorized_term && $cat->term_id === $uncategorized_term->term_id) {
                        continue; // Skip uncategorized
                    }
                    $new_cat_name = $cat->name;
                    $term = get_term_by('name', $new_cat_name, 'ld_course_category');
                    if ($term && !is_wp_error($term)) {
                        $categories[] = $term->term_id;
                    } else {
                        $new_slug = sanitize_title($new_cat_name);
                        $existing_term_by_slug = get_term_by('slug', $new_slug, 'ld_course_category');
                        if ($existing_term_by_slug && !is_wp_error($existing_term_by_slug)) {
                            $categories[] = $existing_term_by_slug->term_id;
                        } else {
                            $term = wp_insert_term($new_cat_name, 'ld_course_category', array('slug' => $new_slug));
                            if (!is_wp_error($term)) {
                                $categories[] = $term['term_id'];
                            } else {
                                error_log(sprintf(__('Failed to create new category "%s" with slug "%s": %s', 'ld-course-grid'), $new_cat_name, $new_slug, $term->get_error_message()));
                                $errors[] = sprintf(__('Failed to create category "%s" for course "%s".', 'ld-course-grid'), $new_cat_name, $title);
                            }
                        }
                    }
                }
            } else {
                $errors[] = sprintf(__('No categories found for course "%s" (no matching categories found).', 'ld-course-grid'), $title);
            }

            // Set categories for the ld_course post
            if (!empty($categories)) {
                $result = wp_set_object_terms($new_post_id, $categories, 'ld_course_category', false);
                if (is_wp_error($result)) {
                    $errors[] = sprintf(__('Failed to set categories for course "%s": %s', 'ld-course-grid'), $title, $result->get_error_message());
                }
            }

            // Set tags
            if (!empty($tags)) {
                wp_set_post_tags($new_post_id, $tags, false);
            }

            // Copy featured image
            $thumbnail_id = get_post_thumbnail_id($sfwd_course_id);
            if ($thumbnail_id) {
                set_post_thumbnail($new_post_id, $thumbnail_id);
            }

            // Set language
            $language_to_set = $default_language;
            $title_lower = strtolower($title);
            if (strpos($title_lower, 'greek') !== false || strpos($title_lower, 'gr') !== false || strpos($title_lower, 'ελληνικά') !== false) {
                $language_to_set = 'gr';
            } elseif (strpos($title_lower, 'english') !== false || strpos($title_lower, 'en') !== false || strpos($title_lower, 'eng') !== false) {
                $language_to_set = 'en';
            }

            $term = get_term_by('slug', $language_to_set, 'ld_language');
            if ($term && !is_wp_error($term)) {
                wp_set_object_terms($new_post_id, $language_to_set, 'ld_language', false);
            } else {
                $errors[] = sprintf(__('Could not set language "%s" for course "%s".', 'ld-course-grid'), $language_to_set, $title);
            }
        }
    }

    wp_reset_postdata();

    $message = sprintf(
        __('Import completed: %d courses imported, %d updated, %d skipped.', 'ld-course-grid'),
        $imported,
        $updated,
        $skipped
    );
    if (!empty($errors)) {
        $message .= '<br>' . __('Errors:', 'ld-course-grid') . '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>';
    }

    wp_send_json_success(array('message' => $message));
}
add_action('wp_ajax_ld_course_grid_import_sfwd_courses', 'ld_course_grid_import_sfwd_courses');