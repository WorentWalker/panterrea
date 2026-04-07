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
            <?php get_template_part('template-parts/search-input', null, ['width' => 'full']); ?>
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
