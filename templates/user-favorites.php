<?php
/**
 * Template Name: User Favorites
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    wp_redirect(URL_LOGIN);
    exit;
}

$user_id = get_current_user_id();
$email_confirmed = get_user_meta($user_id, 'email_verified', true);

if (!$email_confirmed) {
    setMessageCookies('warning', __('Підтвердіть свою електронну пошту для доступу.', 'panterrea_v1'));
    wp_redirect(home_url());
    exit;
}

$favorites = get_user_meta($user_id, 'favorite_posts', true);

if (!$favorites) {
    $favorites = [];
}

get_header();
?>

    <main id="infiniteScroll" class="favoritesPage firstSectionPadding" data-scroll-page="favorites">

        <?php if (!empty($favorites)):
            get_template_part('template-parts/catalog', null, [
                'title' => __('Обрані оголошення', 'panterrea_v1'),
                'query_args' => [
                    'post_type' => 'catalog_post',
                    'posts_per_page' => 12,
                    'post__in' => $favorites,
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
            ]);
        else: ?>
            <div class="container">
                <?php get_template_part('template-parts/empty-folder', null, [
                    'title' => __('Обрані оголошення', 'panterrea_v1'),
                    'info' => __('У вас немає вибраних оголошень', 'panterrea_v1')
                ]); ?>
            </div>
        <?php endif; ?>

    </main>

<?php
get_footer();
