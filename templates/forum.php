<?php
/**
 * Template Name: Forum
 */

if (!defined('ABSPATH')) {
    exit;
}

/*if (!is_user_logged_in()) {
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

$user_name = get_user_meta($user_id, 'name', true);
$user_city = get_user_meta($user_id, 'city', true);
$user_email = get_userdata($user_id)->user_email;
$user_profession = get_user_meta($user_id, 'profession', true);*/

if (is_user_logged_in()) {
    $user_id = get_current_user_id();
    $user_name = get_user_meta($user_id, 'name', true);
    $user_city = get_user_meta($user_id, 'city', true);
    $user_email = get_userdata($user_id)->user_email;
    $user_profession = get_user_meta($user_id, 'profession', true);
} else {
    $user_id = 0;
    $user_name = __('Гість', 'panterrea_v1');
    $user_city = '';
    $user_email = '';
    $user_profession = '';
}

get_header();

/*global $currentLang;*/
?>

<main id="forum" class="forum firstSectionPadding">
    <div class="container">
        <div class="forum__inner">
            <div class="forum__topBlock">
                <div class="forum__topBlock__left">
                    <h2 class="forum__title h4">
                        <?= esc_html__('Форум', 'panterrea_v1'); ?>
                    </h2>

                    <?php forum_breadcrumbs(); ?>

                </div>
                <div class="forum__topBlock__right">
                    <input id="searchInputForum" type="text" name="search"
                        placeholder="<?php _e('Пошук', 'panterrea_v1'); ?>" class="input input__searchForum body2"
                        aria-label="Search">
                    <div class="btn__searchForum"></div>
                </div>
            </div>

            <div class="forum__contentBlock">
                <div class="forum__contentBlock__left">
                    <div class="forum__info">
                        <div class="forum__info__title h6 <?php echo !is_user_logged_in() ? 'is-guest' : ''; ?>">
                            <?php if ($user_name) : ?>
                            <?php echo esc_html($user_name); ?>
                            <?php endif; ?>
                        </div>

                        <?php if ( !is_user_logged_in() ) : ?>
                        <div class="forum__info__subtitle body2">
                            <a href="<?= esc_url(URL_LOGIN) ?>">Авторизуйтесь</a>, щоб писати та коментувати
                        </div>
                        <?php endif; ?>

                        <div class="forum__info__list">
                            <?php if ($user_city) : ?>
                            <div class="forum__info__icon forum__info__city body2"><?php echo esc_html($user_city); ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($user_email) : ?>
                            <div class="forum__info__icon forum__info__email body2"><?php echo esc_html($user_email); ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($user_profession) : ?>
                            <div class="forum__info__icon forum__info__profession body2">
                                <?php echo esc_html($user_profession); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (is_user_logged_in()) : ?>
                    <div class="forum__toggleAll button-large js-toggleAll" data-show="all">
                        <?php _e('Показати мої', 'panterrea_v1'); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="forum__contentBlock__right">
                    <div class="forum__addPost <?php echo !is_user_logged_in() ? 'is-guest' : ''; ?>">

                        <form id="formForum" class="form form__forum" enctype="multipart/form-data">

                            <div class="input__formTextarea">

                                <div id="quillEditor"></div>
                                <input type="hidden" name="postContent" id="postContent">

                                <div class="input__formTextarea__helperVal">
                                    <span class="error caption"></span>
                                </div>
                            </div>

                            <input type="file" id="mediaUploadInput" accept="image/*,video/*" style="display: none;"
                                multiple>
                            <div id="previewContainer" class="input__previewContainer">

                            </div>

                            <div class="form__rowBtn">
                                <div class="btn btn__forumUpload button-medium"><?= __('Прикріпити', 'panterrea_v1') ?>
                                </div>
                                <div class="form__editBtn hidden">
                                    <div class="btn btn__transparent button-medium js-forumEditCancel">
                                        <?= __('Скасувати', 'panterrea_v1') ?></div>
                                    <div class="btn btn__submit button-medium js-forumEdit">
                                        <?= __('Редагувати', 'panterrea_v1') ?></div>
                                </div>
                                <button class="btn btn__submit button-medium js-forumPublish"
                                    type="submit"><?= __('Опублікувати', 'panterrea_v1') ?></button>
                            </div>

                        </form>

                    </div>

                    <?php
                        $highlight_post_id = isset($_GET['highlight_post']) ? intval($_GET['highlight_post']) : 0;

                        if ($highlight_post_id) : ?>
                    <div class="forum__sharedPost">
                        <?php
                                $highlight_args = [
                                    'post_type' => 'post',
                                    'p' => $highlight_post_id,
                                    'meta_query' => [
                                        [
                                            'key' => '_is_forum_post',
                                            'value' => '1',
                                            'compare' => '=',
                                        ],
                                    ],
                                ];
                                $highlight_query = new WP_Query($highlight_args);

                                if ($highlight_query->have_posts()) :
                                    while ($highlight_query->have_posts()) : $highlight_query->the_post();
                                        get_template_part('template-parts/forum-item');
                                    endwhile;
                                    wp_reset_postdata();
                                else : ?>
                        <div class="forum__itemPost">
                            <div class="forum__itemPost__content body2"><?= __('Пост не знайдено.', 'panterrea_v1') ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php
                        // Server-render the forum feed for SEO and no-JS fallback
                        $paged_var = get_query_var('paged');
                        if (!$paged_var) {
                            $paged_var = get_query_var('page');
                        }
                        $paged = $paged_var ? (int) $paged_var : (isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1);

                        $search_query = '';
                        if (isset($_GET['q'])) {
                            $search_query = sanitize_text_field(wp_unslash($_GET['q']));
                        } elseif (isset($_GET['s'])) {
                            $search_query = sanitize_text_field(wp_unslash($_GET['s']));
                        }

                        $only_my = (isset($_GET['only_my']) && $_GET['only_my'] === '1' && is_user_logged_in());

                        $args = [
                            'post_type' => 'post',
                            'posts_per_page' => 10,
                            'orderby' => 'date',
                            'order' => 'DESC',
                            'paged' => $paged,
                            's' => $search_query,
                            'meta_query' => [
                                [
                                    'key' => '_is_forum_post',
                                    'value' => '1',
                                    'compare' => '=',
                                ],
                            ],
                        ];
                        if ($only_my) {
                            $args['author'] = get_current_user_id();
                        }

                        $query = new WP_Query($args);

                        if ($query->have_posts()) : ?>
                    <div id="infiniteScrollForum"
                        class="forum__listPost <?php echo !is_user_logged_in() ? 'is-guest' : ''; ?>"
                        data-current-page="<?php echo esc_attr($paged); ?>"
                        data-max-pages="<?php echo esc_attr($query->max_num_pages); ?>">
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <?php get_template_part('template-parts/forum-item'); ?>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                    <?php
                        // Pagination for non-JS users
                        $base = add_query_arg('paged', '%#%');
                        if ($search_query !== '') {
                            $base = add_query_arg('q', rawurlencode($search_query), $base);
                        }
                        if ($only_my) {
                            $base = add_query_arg('only_my', '1', $base);
                        }

                        $pagination = paginate_links([
                            'base' => $base,
                            'format' => '',
                            'current' => max(1, $paged),
                            'total' => $query->max_num_pages,
                            'type' => 'list',
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                        ]);

                        if ($pagination) : ?>
                    <nav class="forum__pagination">
                        <?php echo $pagination; ?>
                    </nav>
                    <?php endif; ?>
                    <?php else : ?>
                    <div class="forum__itemPost">
                        <div class="forum__itemPost__content body2">
                            <?= __('Стрічка поки що пуста. Ви можете бути першим!', 'panterrea_v1') ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

    <div id="mediaPopup" class="popUp js-popUp mediaPopup hidden" style="display:none;">
        <div class="mediaPopup__content">
            <div class="mediaSlider"></div>
            <div class="mediaPopup__close"></div>
            <div class="mediaPopup__prev forum-slick-prev"></div>
            <div class="mediaPopup__next forum-slick-next"></div>
        </div>
    </div>

    <div id="deleteForumItem" class="popUp js-popUp hidden">
        <div class="container">
            <div class="popUp__inner confirm">
                <div class="popUp__confirm">

                    <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/icon_delete_ad.svg'); ?>"
                        alt="Empty">

                    <div class="popUp__confirm__title h3">
                        <?php _e('Ви дійсно хочете видалити публікацію?', 'panterrea_v1'); ?></div>
                    <div class="popUp__confirm__text body2">
                        <?php _e('Публікація буде видалена назавжди', 'panterrea_v1'); ?></div>

                    <div id="deleteForumItemButton" class="btn btn__submit button-large">
                        <?php _e('Так, видалити', 'panterrea_v1'); ?></div>
                    <div class="btn btn__cancelConfirm subtitle2 js-closePopUp" data-popUp="deleteForumItem">
                        <?php _e('Скасувати', 'panterrea_v1'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div id="deleteForumComment" class="popUp js-popUp hidden">
        <div class="container">
            <div class="popUp__inner confirm">
                <div class="popUp__confirm">

                    <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/icon_delete_ad.svg'); ?>"
                        alt="Empty">

                    <div class="popUp__confirm__title h3">
                        <?php _e('Ви дійсно хочете видалити коментар?', 'panterrea_v1'); ?></div>
                    <div class="popUp__confirm__text body2">
                        <?php _e('Коментар буде видалена назавжди', 'panterrea_v1'); ?></div>

                    <div id="deleteForumCommentButton" class="btn btn__submit button-large">
                        <?php _e('Так, видалити', 'panterrea_v1'); ?></div>
                    <div class="btn btn__cancelConfirm subtitle2 js-closePopUp" data-popUp="deleteForumComment">
                        <?php _e('Скасувати', 'panterrea_v1'); ?></div>
                </div>
            </div>
        </div>
    </div>

</main>

<?php
get_footer();