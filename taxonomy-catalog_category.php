<?php

/**
 * Catalog Category/Subcategory
 */

get_header();

$current_category = get_queried_object();

if ($current_category && $current_category->parent != 0) { ?>

<main id="infiniteScroll" class="catalogSubCategory firstSectionPadding">

    <?php get_template_part('template-parts/catalog-subcategory'); ?>

    <?php get_template_part('template-parts/catalog', null, [
            'category' => $current_category->slug,
            'breadcrumbs' => false,
            'query_args' => [
                'post_type' => 'catalog_post',
                'posts_per_page' => 9,
                'tax_query' => [
                    [
                        'taxonomy' => 'catalog_category',
                        'field'    => 'term_id',
                        'terms'    => [$current_category->term_id],
                        'operator' => 'IN',
                    ],
                ],
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
            ],
            'sort' => true
        ]); ?>


</main>

<?php } else { ?>

<main id="infiniteScroll" class="firstSectionPadding">

    <?php get_template_part('template-parts/catalog-category'); ?>

    <?php get_template_part('template-parts/catalog', null, [
            'category' => $current_category->slug,
            'title' => __('Кращі пропозиції', 'panterrea_v1'),
            'terms_args' => [
                'taxonomy' => 'catalog_category',
                'hide_empty' => false,
                'meta_key' => 'category_order',
                'orderby' => 'meta_value_num',
                'order' => 'ASC',
                'parent' => $current_category->term_id,
            ],
            'query_args' => [
                'post_type' => 'catalog_post',
                'posts_per_page' => 9,
                'tax_query' => [
                    [
                        'taxonomy' => 'catalog_category',
                        'field'    => 'term_id',
                        'terms'    => [$current_category->term_id],
                        'operator' => 'IN',
                    ],
                ],
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
            ],
            'sort' => true
        ]); ?>


</main>

<?php
}

get_footer();