<?php
/**
 * Template Name: User Ad
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

$publish_args = array(
    'post_type' => 'catalog_post',
    'posts_per_page' => -1,
    'author' => $user_id,
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
);

$publish_query = new WP_Query($publish_args);
$total_publish_posts = $publish_query->found_posts;

$deactivated_args = array(
    'post_type' => 'catalog_post',
    'posts_per_page' => -1,
    'author' => $user_id,
    'meta_query' => [
        [
            'key'     => '_is_active',
            'value'   => '0',
            'compare' => '='
        ]
    ],
    'orderby' => [
        'meta_value_num' => 'DESC',
        'date' => 'DESC'
    ],
    'meta_key' => '_is_boosted'
);

$deactivated_query = new WP_Query($deactivated_args);
$total_deactivated_posts = $deactivated_query->found_posts;

$pending_args = array(
    'post_type' => 'catalog_post',
    'posts_per_page' => -1,
    'author' => $user_id,
    'post_status' => 'pending',
    'orderby' => 'date',
    'order' => 'DESC'
);

$pending_query = new WP_Query($pending_args);
$total_pending_posts = $pending_query->found_posts;

get_header();
?>

<main class="userAdPage firstSectionPadding">

    <section class="catalog">
        <div class="container">
            <div class="catalog__inner">
                <h1 class="catalog__title h3"><?php _e('Мої оголошення', 'panterrea_v1'); ?></h1>

                <div class="userAdPage__tabs js-tabs">
                    <div role="tablist" class="userAdPage__tabs__titles">
                        <div role="tab" data-tabpanel-id="tabpanel-1" class="userAdPage__tabs__title active">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M12 20H8C4.229 20 2.343 20 1.172 18.828C-1.19209e-07 17.657 0 15.771 0 12V8C0 4.229 -1.19209e-07 2.343 1.172 1.172C2.343 -1.19209e-07 4.239 0 8.03 0C8.636 0 9.121 -4.09782e-08 9.53 0.017C9.517 0.097 9.51 0.178 9.51 0.261L9.5 3.095C9.5 4.192 9.5 5.162 9.605 5.943C9.719 6.79 9.98 7.637 10.672 8.329C11.362 9.019 12.21 9.281 13.057 9.395C13.838 9.5 14.808 9.5 15.905 9.5H19.957C20 10.034 20 10.69 20 11.563V12C20 15.771 20 17.657 18.828 18.828C17.657 20 15.771 20 12 20ZM3.25 12.5C3.25 12.3011 3.32902 12.1103 3.46967 11.9697C3.61032 11.829 3.80109 11.75 4 11.75H12C12.1989 11.75 12.3897 11.829 12.5303 11.9697C12.671 12.1103 12.75 12.3011 12.75 12.5C12.75 12.6989 12.671 12.8897 12.5303 13.0303C12.3897 13.171 12.1989 13.25 12 13.25H4C3.80109 13.25 3.61032 13.171 3.46967 13.0303C3.32902 12.8897 3.25 12.6989 3.25 12.5ZM3.25 16C3.25 15.8011 3.32902 15.6103 3.46967 15.4697C3.61032 15.329 3.80109 15.25 4 15.25H9.5C9.69891 15.25 9.88968 15.329 10.0303 15.4697C10.171 15.6103 10.25 15.8011 10.25 16C10.25 16.1989 10.171 16.3897 10.0303 16.5303C9.88968 16.671 9.69891 16.75 9.5 16.75H4C3.80109 16.75 3.61032 16.671 3.46967 16.5303C3.32902 16.3897 3.25 16.1989 3.25 16Z" />
                                <path
                                    d="M17.352 5.617L13.392 2.054C12.265 1.039 11.702 0.531 11.009 0.266L11 3C11 5.357 11 6.536 11.732 7.268C12.464 8 13.643 8 16 8H19.58C19.218 7.296 18.568 6.712 17.352 5.617Z" />
                            </svg>
                            <span class="subtitle2"><?php _e('Активні', 'panterrea_v1'); ?></span>
                            <div class="count label-text"><?php echo esc_html($total_publish_posts); ?></div>
                        </div>
                        <div role="tab" data-tabpanel-id="tabpanel-2" class="userAdPage__tabs__title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="18" viewBox="0 0 20 18"
                                fill="none">
                                <path
                                    d="M0 2C0 1.057 -2.98023e-08 0.586 0.293 0.293C0.586 -2.98023e-08 1.057 0 2 0H18C18.943 0 19.414 -2.98023e-08 19.707 0.293C20 0.586 20 1.057 20 2C20 2.943 20 3.414 19.707 3.707C19.414 4 18.943 4 18 4H2C1.057 4 0.586 4 0.293 3.707C-2.98023e-08 3.414 0 2.943 0 2Z" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M18.069 5.5C18.21 5.5 18.355 5.5 18.5 5.498V10C18.5 13.771 18.5 15.657 17.328 16.828C16.157 18 14.271 18 10.5 18H9.5C5.729 18 3.843 18 2.672 16.828C1.5 15.657 1.5 13.771 1.5 10V5.498C1.645 5.5 1.79 5.5 1.931 5.5H18.069ZM7 9C7 8.534 7 8.301 7.076 8.117C7.17749 7.87208 7.37208 7.67749 7.617 7.576C7.801 7.5 8.034 7.5 8.5 7.5H11.5C11.966 7.5 12.199 7.5 12.383 7.576C12.6275 7.67771 12.8217 7.87227 12.923 8.117C13 8.301 13 8.534 13 9C13 9.466 13 9.699 12.924 9.883C12.8223 10.1275 12.6277 10.3217 12.383 10.423C12.199 10.5 11.966 10.5 11.5 10.5H8.5C8.034 10.5 7.801 10.5 7.617 10.424C7.37246 10.3223 7.17826 10.1277 7.077 9.883C7 9.699 7 9.466 7 9Z" />
                            </svg>
                            <span class="subtitle2"><?php _e('Неактивні', 'panterrea_v1'); ?></span>
                            <div class="count label-text"><?php echo esc_html($total_deactivated_posts); ?></div>
                        </div>
                        <div role="tab" data-tabpanel-id="tabpanel-3" class="userAdPage__tabs__title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M10 0C4.477 0 0 4.477 0 10C0 15.523 4.477 20 10 20C15.523 20 20 15.523 20 10C20 4.477 15.523 0 10 0ZM10 5C10.1989 5 10.3897 5.07902 10.5303 5.21967C10.671 5.36032 10.75 5.55109 10.75 5.75V10.25C10.75 10.4489 10.671 10.6397 10.5303 10.7803C10.3897 10.921 10.1989 11 10 11C9.80109 11 9.61032 10.921 9.46967 10.7803C9.32902 10.6397 9.25 10.4489 9.25 10.25V5.75C9.25 5.55109 9.32902 5.36032 9.46967 5.21967C9.61032 5.07902 9.80109 5 10 5ZM10 15C10.2652 15 10.5196 14.8946 10.7071 14.7071C10.8946 14.5196 11 14.2652 11 14C11 13.7348 10.8946 13.4804 10.7071 13.2929C10.5196 13.1054 10.2652 13 10 13C9.73478 13 9.48043 13.1054 9.29289 13.2929C9.10536 13.4804 9 13.7348 9 14C9 14.2652 9.10536 14.5196 9.29289 14.7071C9.48043 14.8946 9.73478 15 10 15Z" />
                            </svg>
                            <span class="subtitle2"><?php _e('На модерації', 'panterrea_v1'); ?></span>
                            <div class="count label-text"><?php echo esc_html($total_pending_posts); ?></div>
                        </div>
                    </div>

                    <div role="tabpanel" id="tabpanel-1" class="userAdPage__tabs__content active">

                        <div id="catalogPublishItems" class="catalog__items">
                            <?php
                                if ($publish_query->have_posts()) :
                                    while ($publish_query->have_posts()) : $publish_query->the_post();
                                        get_template_part('template-parts/catalog-item', null, [
                                            'favorites' => $favorites
                                        ]);
                                    endwhile;
                                    wp_reset_postdata();
                                else :
                                    get_template_part('template-parts/empty-folder', null, [
                                        'info' => __('У вас немає активних оголошень', 'panterrea_v1')
                                    ]);
                                endif;
                                ?>
                        </div>

                    </div>

                    <div role="tabpanel" id="tabpanel-2" class="userAdPage__tabs__content ">

                        <div id="catalogDeactivatedItems" class="catalog__items">
                            <?php
                                if ($deactivated_query->have_posts()) :
                                    while ($deactivated_query->have_posts()) : $deactivated_query->the_post();
                                        get_template_part('template-parts/catalog-item', null, [
                                            'favorites' => $favorites
                                        ]);
                                    endwhile;
                                    wp_reset_postdata();
                                else :
                                    get_template_part('template-parts/empty-folder', null, [
                                        'info' => __('У вас немає неактивних оголошень', 'panterrea_v1')
                                    ]);
                                endif;
                                ?>
                        </div>

                    </div>

                    <div role="tabpanel" id="tabpanel-3" class="userAdPage__tabs__content ">

                        <div id="catalogPendingItems" class="catalog__items">
                            <?php
                                if ($pending_query->have_posts()) :
                                    while ($pending_query->have_posts()) : $pending_query->the_post();
                                        get_template_part('template-parts/catalog-item', null, [
                                            'favorites' => $favorites
                                        ]);
                                    endwhile;
                                    wp_reset_postdata();
                                else :
                                    get_template_part('template-parts/empty-folder', null, [
                                        'info' => __('У вас немає оголошень на модерації', 'panterrea_v1')
                                    ]);
                                endif;
                                ?>
                        </div>

                    </div>

                </div>

            </div>
        </div>
    </section>

</main>

<?php
get_footer();