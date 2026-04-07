<?php
/**
 * Template Name: Catalog Author
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();

$favorites = get_user_meta($user_id, 'favorite_posts', true);

if (!$favorites) {
    $favorites = [];
}

get_header();
?>

    <main id="infiniteScroll" class="authorPage firstSectionPadding" data-scroll-page="author">

        <?php if (!empty($favorites)):
            get_template_part('template-parts/catalog', null, [
                'title' => 'Обрані оголошення',
                'query_args' => [
                    'post_type' => 'catalog_post',
                    'posts_per_page' => 12,
                    'post__in' => $favorites,
                ]
            ]);
        else: ?>
            <div class="container">
                <?php get_template_part('template-parts/empty-folder', null, [
                    'title' => 'Обрані оголошення',
                    'info' => 'У вас немає вибраних оголошень'
                ]); ?>
            </div>
        <?php endif; ?>

    </main>

<?php
get_footer();
