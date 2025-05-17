<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Shortcode for Course Grid with Pagination and Search
function ld_course_grid_shortcode($atts) {
    ob_start();

    $categories = get_terms(array('taxonomy' => 'ld_course_category', 'hide_empty' => true));
    $tags = get_tags(array('hide_empty' => true));
    $languages = get_terms(array('taxonomy' => 'ld_language', 'hide_empty' => true));

    // Get current filter and search values from query vars
    $current_category = isset($_GET['course_category']) ? sanitize_text_field($_GET['course_category']) : '';
    $current_tag = isset($_GET['course_tag']) ? sanitize_text_field($_GET['course_tag']) : '';
    $current_language = isset($_GET['course_language']) ? sanitize_text_field($_GET['course_language']) : '';
    $current_price_min = isset($_GET['price_min']) && $_GET['price_min'] !== '' ? floatval($_GET['price_min']) : '';
    $current_price_max = isset($_GET['price_max']) && $_GET['price_max'] !== '' ? floatval($_GET['price_max']) : '';
    $search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

    // Calculate price range for slider
    $price_args = array(
        'post_type'      => 'ld_course',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => array(
            array(
                'key'     => '_ld_course_price',
                'compare' => 'EXISTS',
            ),
        ),
    );
    $price_query = new WP_Query($price_args);
    $prices = array(0); // Include 0 for free courses
    if ($price_query->have_posts()) {
        foreach ($price_query->posts as $post_id) {
            $price = get_post_meta($post_id, '_ld_course_price', true);
            if ($price !== '') {
                $prices[] = floatval($price);
            }
        }
    }
    wp_reset_postdata();
    $min_price = min($prices);
    $max_price = max($prices);
    $max_price = ceil($max_price) > 0 ? ceil($max_price) : 100;
    $min_price = floor($min_price);

    // Query to get total posts for pagination
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
    if ($current_category) {
        $tax_query[] = array(
            'taxonomy' => 'ld_course_category',
            'field'    => 'slug',
            'terms'    => $current_category,
        );
    }
    if ($current_tag) {
        $tax_query[] = array(
            'taxonomy' => 'post_tag',
            'field'    => 'slug',
            'terms'    => $current_tag,
        );
    }
    if ($current_language) {
        $tax_query[] = array(
            'taxonomy' => 'ld_language',
            'field'    => 'slug',
            'terms'    => $current_language,
        );
    }
    if (count($tax_query) > 1) {
        $total_args['tax_query'] = $tax_query;
    }

    $meta_query = array('relation' => 'AND');
    if ($current_price_min !== '') {
        $meta_query[] = array(
            'key'     => '_ld_course_price',
            'value'   => $current_price_min,
            'compare' => '>=',
            'type'    => 'DECIMAL(10,2)',
        );
    }
    if ($current_price_max !== '') {
        $meta_query[] = array(
            'key'     => '_ld_course_price',
            'value'   => $current_price_max,
            'compare' => '<=',
            'type'    => 'DECIMAL(10,2)',
        );
    }
    if ($current_price_min === 0) {
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
    if (count($meta_query) > 1 || (count($meta_query) == 1 && $current_price_min === 0)) {
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

    // Initial course query for the current page
    $args = array(
        'post_type'      => 'ld_course',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged'          => $current_page,
    );

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

    // Execute the query without modifying global $wp_query
    $query = new WP_Query($args);

    ?>
    <div class="ld-course-grid-search">
        <form id="ld-course-search-form">
            <input type="text" id="course_search" name="s" value="<?php echo esc_attr($search_term); ?>" placeholder="<?php _e('Search courses by title or category...', 'ld-course-grid'); ?>">
            <button type="submit"><?php _e('Search', 'ld-course-grid'); ?></button>
        </form>
    </div>

    <div class="ld-course-grid-filter">
        <form id="ld-course-filter-form">
            <div class="filter-group">
                <label for="course_category"><?php _e('Category', 'ld-course-grid'); ?></label>
                <select name="course_category" id="course_category">
                    <option value=""><?php _e('All Categories', 'ld-course-grid'); ?></option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo esc_attr($cat->slug); ?>" <?php selected($current_category, $cat->slug); ?>>
                            <?php echo esc_html($cat->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="course_tag"><?php _e('Tag', 'ld-course-grid'); ?></label>
                <select name="course_tag" id="course_tag">
                    <option value=""><?php _e('All Tags', 'ld-course-grid'); ?></option>
                    <?php foreach ($tags as $tag): ?>
                        <option value="<?php echo esc_attr($tag->slug); ?>" <?php selected($current_tag, $tag->slug); ?>>
                            <?php echo esc_html($tag->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="course_language"><?php _e('Language', 'ld-course-grid'); ?></label>
                <select name="course_language" id="course_language">
                    <option value=""><?php _e('All Languages', 'ld-course-grid'); ?></option>
                    <?php foreach ($languages as $lang): ?>
                        <option value="<?php echo esc_attr($lang->slug); ?>" <?php selected($current_language, $lang->slug); ?>>
                            <?php echo esc_html($lang->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group price-range">
                <label><?php _e('Price Range', 'ld-course-grid'); ?></label>
                <div id="price-slider"></div>
                <div id="price-display">
                    <?php
                    $display_min = $current_price_min !== '' ? number_format($current_price_min, 2) : number_format($min_price, 2);
                    $display_max = $current_price_max !== '' ? number_format($current_price_max, 2) : number_format($max_price, 2);
                    printf(esc_html__('Price Range: €%s - €%s', 'ld-course-grid'), $display_min, $display_max);
                    ?>
                </div>
            </div>
            <div class="filter-group filter-button">
                <button type="button" id="ld-course-filter-btn"><?php _e('Filter', 'ld-course-grid'); ?></button>
                <button type="button" id="ld-course-reset-btn"><?php _e('Reset', 'ld-course-grid'); ?></button>
            </div>
        </form>
    </div>

    <div class="ld-course-grid" id="ld-course-grid">
        <?php if ($query->have_posts()): ?>
            <?php while ($query->have_posts()): $query->the_post(); ?>
                <?php
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
                        <h3 class="ld-course-card-title"><?php the_title(); ?></h3>
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
            <?php endwhile; ?>
        <?php else: ?>
            <p><?php _e('No courses found. Try adjusting your search or filters.', 'ld-course-grid'); ?></p>
        <?php endif; ?>
    </div>
    <div class="ld-course-pagination" id="ld-course-pagination">
        <?php echo ld_course_grid_custom_pagination($current_page, $max_pages); ?>
    </div>

    <script type="text/javascript">
        var ldCourseGridMaxPages = <?php echo $max_pages; ?>;
        var ldCourseGridCurrentPage = <?php echo $current_page; ?>;
        var ldCourseGridFilters = {
            course_category: '<?php echo esc_js($current_category); ?>',
            tag: '<?php echo esc_js($current_tag); ?>',
            language: '<?php echo esc_js($current_language); ?>',
            price_min: '<?php echo esc_js($current_price_min); ?>',
            price_max: '<?php echo esc_js($current_price_max); ?>',
            s: '<?php echo esc_js($search_term); ?>'
        };
        var ldCourseGridPriceRange = {
            min: <?php echo floatval($min_price); ?>,
            max: <?php echo floatval($max_price); ?>
        };
    </script>
    <?php
    // Remove the filter to avoid affecting other queries
    if (!empty($search_term)) {
        remove_filter('posts_where', function() {});
    }
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('ld_course_grid', 'ld_course_grid_shortcode');