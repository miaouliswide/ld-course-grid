jQuery(document).ready(function($) {
    // Filter button click handler
    $('#ld-course-filter-btn').on('click', function() {
        var filters = {
            category: $('#course_category').val(),
            tag: $('#course_tag').val(),
            language: $('#course_language').val(),
            price_min: $('#price-slider').data('min') || '',
            price_max: $('#price-slider').data('max') || '',
            s: $('#course_search').val() || ''
        };
        loadCourses(1, filters);
    });

    // Reset button click handler
    $('#ld-course-reset-btn').on('click', function() {
        $('#course_category, #course_tag, #course_language, #course_search').val('');
        $('#price-slider').val([ldCourseGridPriceRange.min, ldCourseGridPriceRange.max]);
        $('#price-display').text(sprintf(ldCourseGrid.i18n.price_range, ldCourseGridPriceRange.min, ldCourseGridPriceRange.max));
        loadCourses(1, {});
    });

    // Load courses via AJAX
    function loadCourses(page, filters) {
        $.ajax({
            url: ldCourseGrid.ajax_url,
            type: 'POST',
            data: $.extend({
                action: 'ld_course_grid_pagination',
                nonce: ldCourseGrid.nonce,
                page: page
            }, filters),
            beforeSend: function() {
                $('#ld-course-grid').html('<p>' + ldCourseGrid.i18n.loading + '</p>');
                $('#ld-course-pagination').html('');
            },
            success: function(response) {
                if (response.success) {
                    $('#ld-course-grid').html(response.data.html);
                    $('#ld-course-pagination').html(response.data.pagination || '');
                    ldCourseGridMaxPages = response.data.max_pages;
                    ldCourseGridCurrentPage = response.data.page;
                    updateURL(response.data.filters);
                } else {
                    $('#ld-course-grid').html('<p style="color: red;">' + ldCourseGrid.i18n.error_loading_with_message + response.data.message + '</p>');
                    $('#ld-course-pagination').html('');
                }
            },
            error: function(xhr, status, error) {
                $('#ld-course-grid').html('<p style="color: red;">' + ldCourseGrid.i18n.error_loading_with_message + error + '</p>');
                $('#ld-course-pagination').html('');
            }
        });
    }

    // Pagination click handler
    $(document).on('click', '.ld-course-pagination .page-link', function(e) {
        e.preventDefault();
        if ($(this).hasClass('disabled')) {
            return;
        }
        var page = $(this).data('page') || 1;
        var filters = {
            category: $('#course_category').val(),
            tag: $('#course_tag').val(),
            language: $('#course_language').val(),
            price_min: $('#price-slider').data('min') || '',
            price_max: $('#price-slider').data('max') || '',
            s: $('#course_search').val() || ''
        };
        loadCourses(page, filters);
    });

    // Price slider initialization
    var priceSlider = document.getElementById('price-slider');
    noUiSlider.create(priceSlider, {
        start: [ldCourseGridFilters.price_min || ldCourseGridPriceRange.min, ldCourseGridFilters.price_max || ldCourseGridPriceRange.max],
        connect: true,
        range: {
            'min': ldCourseGridPriceRange.min,
            'max': ldCourseGridPriceRange.max
        }
    });
    priceSlider.noUiSlider.on('update', function(values, handle) {
        var min = parseFloat(values[0]).toFixed(2);
        var max = parseFloat(values[1]).toFixed(2);
        $('#price-display').text(sprintf(ldCourseGrid.i18n.price_range, min, max));
        priceSlider.setAttribute('data-min', min);
        priceSlider.setAttribute('data-max', max);
    });

    // Search form submission
    $('#ld-course-search-form').on('submit', function(e) {
        e.preventDefault();
        var searchTerm = $('#course_search').val();
        var filters = {
            s: searchTerm,
            category: $('#course_category').val(),
            tag: $('#course_tag').val(),
            language: $('#course_language').val(),
            price_min: $('#price-slider').data('min') || '',
            price_max: $('#price-slider').data('max') || ''
        };
        loadCourses(1, filters);
    });

    // Update URL with filters
    function updateURL(filters) {
        var url = new URL(window.location);
        url.searchParams.set('s', filters.s || '');
        url.searchParams.set('course_category', filters.course_category || '');
        url.searchParams.set('course_tag', filters.tag || '');
        url.searchParams.set('course_language', filters.language || '');
        url.searchParams.set('price_min', filters.price_min || '');
        url.searchParams.set('price_max', filters.price_max || '');
        url.searchParams.set('paged', ldCourseGridCurrentPage);
        window.history.pushState({}, '', url);
    }

    // Initial load with current filters
    var initialFilters = {
        category: ldCourseGridFilters.course_category,
        tag: ldCourseGridFilters.tag,
        language: ldCourseGridFilters.language,
        price_min: ldCourseGridFilters.price_min,
        price_max: ldCourseGridFilters.price_max,
        s: ldCourseGridFilters.s
    };
    if (initialFilters.price_min || initialFilters.price_max) {
        priceSlider.noUiSlider.set([initialFilters.price_min || ldCourseGridPriceRange.min, initialFilters.price_max || ldCourseGridPriceRange.max]);
    }
    loadCourses(ldCourseGridCurrentPage, initialFilters);
});