<?php
/**
 * Template Name: Author Ad
 */

$author_id = isset($_GET['author_id']) ? intval($_GET['author_id']) : false;
if (!$author_id) {
    wp_redirect(home_url());
    exit;
}

get_header();
?>

    <main id="infiniteScroll" class="authorPage firstSectionPadding" data-scroll-page="author-<?= esc_html($author_id); ?>">

        <?php
        get_template_part('template-parts/catalog', null, [
            'title' => __('Інші оголошення автора', 'panterrea_v1'),
            'author_id' => $author_id,
            'query_args' => [
                'post_type' => 'catalog_post',
                'posts_per_page' => 12,
                'author' => $author_id,
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
            ]
        ]); ?>

    </main>

<?php
get_footer();