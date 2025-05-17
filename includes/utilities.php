<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Custom Pagination Function
function ld_course_grid_custom_pagination($current_page, $max_pages) {
    $pagination_html = '<div class="ld-course-pagination">';
    
    // Previous link
    if ($current_page > 1) {
        $pagination_html .= '<a href="#" class="page-link prev" data-page="' . ($current_page - 1) . '">' . __('Previous', 'ld-course-grid') . '</a>';
    } else {
        $pagination_html .= '<span class="page-link prev disabled">' . __('Previous', 'ld-course-grid') . '</span>';
    }
    
    // Page numbers
    $range = 2; // Number of pages to show before and after the current page
    $start = max(1, $current_page - $range);
    $end = min($max_pages, $current_page + $range);
    
    // Add ellipsis if there are pages before the start
    if ($start > 1) {
        $pagination_html .= '<a href="#" class="page-link" data-page="1">1</a>';
        if ($start > 2) {
            $pagination_html .= '<span class="ellipsis">...</span>';
        }
    }
    
    // Page links
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $pagination_html .= '<span class="page-link current">' . $i . '</span>';
        } else {
            $pagination_html .= '<a href="#" class="page-link" data-page="' . $i . '">' . $i . '</a>';
        }
    }
    
    // Add ellipsis if there are pages after the end
    if ($end < $max_pages) {
        if ($end < $max_pages - 1) {
            $pagination_html .= '<span class="ellipsis">...</span>';
        }
        $pagination_html .= '<a href="#" class="page-link" data-page="' . $max_pages . '">' . $max_pages . '</a>';
    }
    
    // Next link
    if ($current_page < $max_pages) {
        $pagination_html .= '<a href="#" class="page-link next" data-page="' . ($current_page + 1) . '">' . __('Next', 'ld-course-grid') . '</a>';
    } else {
        $pagination_html .= '<span class="page-link next disabled">' . __('Next', 'ld-course-grid') . '</span>';
    }
    
    $pagination_html .= '</div>';
    return $pagination_html;
}