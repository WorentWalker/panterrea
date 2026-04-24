<?php

/**
 * Template Name: Search
 */

if (!defined('ABSPATH')) {
    exit;
}

$search_string = isset($_GET['search_string']) ? sanitize_text_field($_GET['search_string']) : false;

$query_args = [
    'post_type' => 'catalog_post',
    'posts_per_page' => 12,
    's' => $search_string,
    'meta_query' => [
        [
            'key'     => '_is_active',
            'value'   => '1',
            'compare' => '='
        ]
    ],
    'orderby' => [
        'meta_value_num' => 'DESC',
        'date' => 'DESC'
    ],
    'meta_key' => '_is_boosted'
];

get_header();
?>

    <main id="infiniteScroll" class="searchPage firstSectionPadding" data-scroll-page="search-<?= esc_html($search_string); ?>">
        <div class="container">
            <?php catalog_breadcrumbs(); ?>
            <div class="catalog__mobileBar">
                <?php get_template_part('template-parts/search-input', null, ['width' => 'full']); ?>
                <button type="button" class="catalog__filtersToggle js-catalogFiltersToggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
                    <?php esc_html_e('Фільтри', 'panterrea_v1'); ?>
                </button>
            </div>
        </div>

        <?php get_template_part('template-parts/catalog', null, [
            'query_args' => $query_args,
            'breadcrumbs' => false,
            'sort' => true,
            'search' => $search_string
        ]); ?>

    </main>

<?php
get_footer();
