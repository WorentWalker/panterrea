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

$paged_var = get_query_var('paged');
if (!$paged_var) {
    $paged_var = get_query_var('page');
}
$forum_paged = $paged_var ? (int) $paged_var : (isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1);

$forum_search_query = '';
if (isset($_GET['q'])) {
    $forum_search_query = sanitize_text_field(wp_unslash($_GET['q']));
} elseif (isset($_GET['s'])) {
    $forum_search_query = sanitize_text_field(wp_unslash($_GET['s']));
}

$forum_sort = panterrea_forum_get_sort_from_request();
$forum_only_my = ($forum_sort === 'mine');
$forum_cat_ids = panterrea_forum_get_cat_ids_from_request('forum_cat');

$forum_sidebar_categories = get_categories([
    'taxonomy' => 'category',
    'orderby' => 'name',
    'order' => 'ASC',
    'hide_empty' => false,
]);
$forum_sidebar_categories = panterrea_forum_categories_other_last($forum_sidebar_categories);

/*global $currentLang;*/
?>

<main id="forum" class="forum firstSectionPadding">
    <div class="container">
        <div class="forum__inner">
            <div class="forum__topBlock">
                <div class="forum__topBlock__left">
                    <?php forum_breadcrumbs(); ?>

                    <h2 class="forum__title h4">
                        <?= esc_html__('Форум', 'panterrea_v1'); ?>
                    </h2>

                    <p class="forum__subtitle body2">
                        <?= esc_html__('Приєднуйтесь до тисяч користувачів, обговорюючи все: від останніх технологічних трендів до простої краси природи', 'panterrea_v1'); ?>
                    </p>
                </div>
            </div>

            <div class="forum__mobileBar">
                <div class="forum__sidebar__search forum__mobileBar__search">
                    <span class="forum__sidebar__searchIcon" aria-hidden="true"></span>
                    <input type="search" name="search" inputmode="search" autocomplete="off"
                        placeholder="<?php esc_attr_e('Пошук', 'panterrea_v1'); ?>"
                        class="forum__sidebar__searchInput js-forumSearchInput body2"
                        aria-label="<?php esc_attr_e('Пошук у стрічці форуму', 'panterrea_v1'); ?>"
                        value="<?php echo isset($_GET['q']) ? esc_attr(sanitize_text_field(wp_unslash($_GET['q']))) : ''; ?>">
                    <button type="button" class="forum__sidebar__searchSubmit js-forumMobileSearchSubmit"
                        aria-label="<?php esc_attr_e('Шукати', 'panterrea_v1'); ?>"></button>
                </div>
                <button type="button" class="forum__filtersToggle js-forumFiltersToggle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="6" x2="20" y2="6" />
                        <line x1="8" y1="12" x2="16" y2="12" />
                        <line x1="11" y1="18" x2="13" y2="18" />
                    </svg>
                    <?php esc_html_e('Фільтри', 'panterrea_v1'); ?>
                </button>
            </div>

            <div class="forum__body">
                <?php
                get_template_part('template-parts/forum-sidebar', null, [
                    'categories' => $forum_sidebar_categories,
                    'selected_cat_ids' => $forum_cat_ids,
                    'only_my' => $forum_only_my,
                    'forum_sort' => $forum_sort,
                ]);
                ?>

                <div class="forum__main">
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

                    <div id="forumComposer" class="forum__composer is-open">

                        <?php
                        $is_logged = is_user_logged_in();
                        $composer_avatar = $is_logged
                            ? get_avatar_url($user_id, ['size' => 40])
                            : get_template_directory_uri() . '/src/svg/icon_forum_profession.svg';
                        ?>


                        <!-- ── Editor (visible to all; guests get draft flow) ── -->
                        <div class="forum__composer__expanded js-forumComposerExpanded">
                            <div class="forum__composer__editorRow">


                                <form id="formForum" class="form form__forum" enctype="multipart/form-data"
                                    data-logged-in="<?php echo $is_logged ? '1' : '0'; ?>"
                                    data-login-url="<?php echo esc_attr(URL_LOGIN); ?>"
                                    data-register-url="<?php echo esc_attr(URL_REGISTER); ?>">

                                    <div class="input__formTextarea">
                                        <div id="quillEditor"></div>
                                        <input type="hidden" name="postContent" id="postContent">
                                        <div class="input__formTextarea__helperVal">
                                            <span class="error caption"></span>
                                        </div>
                                    </div>

                                    <?php if (!empty($forum_sidebar_categories)) : ?>
                                    <p class="forum__composer__catsLabel caption">
                                        <?php esc_html_e('Категорія допису:', 'panterrea_v1'); ?>
                                    </p>
                                    <div class="forum__composer__cats" role="group"
                                        aria-label="<?php esc_attr_e('Категорії допису', 'panterrea_v1'); ?>">
                                        <?php foreach ($forum_sidebar_categories as $term) :
                                            if (!$term instanceof WP_Term) { continue; }
                                        ?>
                                        <button type="button" class="forum__composer__catChip js-forumCatChip"
                                            data-cat-id="<?php echo esc_attr((string) $term->term_id); ?>"
                                            data-cat-name="<?php echo esc_attr($term->name); ?>" aria-pressed="false">
                                            <?php echo esc_html($term->name); ?>
                                        </button>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    <div id="composerCatInputs" hidden aria-hidden="true"></div>

                                    <input type="file" id="mediaUploadInput" name="files" accept="image/*,video/*"
                                        style="display:none;" multiple>
                                    <div id="previewContainer" class="input__previewContainer"></div>

                                    <div class="forum__composer__toolbar">
                                        <div class="forum__composer__tools">
                                            <button type="button" class="forum__composer__tool btn__forumUpload"
                                                title="<?php esc_attr_e('Додати зображення', 'panterrea_v1'); ?>">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M18.512 10.0771C18.512 10.8161 17.887 11.4151 17.116 11.4151C16.346 11.4151 15.721 10.8151 15.721 10.0771C15.721 9.33812 16.346 8.74012 17.116 8.74012C17.886 8.74012 18.512 9.33812 18.512 10.0771Z"
                                                        fill="#12BF67" />
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M18.036 5.53212C16.976 5.39612 15.622 5.39612 13.913 5.39612H10.087C8.377 5.39612 7.023 5.39612 5.964 5.53212C4.874 5.67312 3.99 5.96912 3.294 6.63612C2.598 7.30412 2.289 8.15012 2.142 9.19612C2 10.2101 2 11.5081 2 13.1471V13.2471C2 14.8861 2 16.1841 2.142 17.2001C2.289 18.2451 2.598 19.0911 3.294 19.7581C3.99 20.4261 4.874 20.7221 5.964 20.8621C7.024 21.0001 8.378 21.0001 10.087 21.0001H13.913C15.623 21.0001 16.977 21.0001 18.036 20.8631C19.126 20.7231 20.01 20.4271 20.706 19.7591C21.402 19.0921 21.711 18.2461 21.858 17.2011C22 16.1851 22 14.8871 22 13.2481V13.1481C22 11.5081 22 10.2111 21.858 9.19512C21.711 8.15012 21.402 7.30412 20.706 6.63612C20.01 5.96912 19.126 5.67312 18.036 5.53212ZM6.15 6.85812C5.214 6.97812 4.675 7.20512 4.28 7.58212C3.887 7.96012 3.651 8.47612 3.525 9.37312C3.425 10.0931 3.402 10.9931 3.397 12.1691L3.867 11.7741C4.992 10.8311 6.686 10.8851 7.742 11.8971L11.732 15.7221C11.9297 15.9086 12.1849 16.0224 12.4558 16.0449C12.7267 16.0675 12.9972 15.9974 13.223 15.8461L13.501 15.6591C14.1531 15.223 14.9297 15.0115 15.7129 15.0566C16.4961 15.1017 17.2433 15.401 17.841 15.9091L20.248 17.9871C20.346 17.7231 20.421 17.4071 20.475 17.0221C20.603 16.1061 20.605 14.8981 20.605 13.1981C20.605 11.4981 20.603 10.2901 20.475 9.37312C20.349 8.47612 20.113 7.96012 19.719 7.58312C19.326 7.20512 18.786 6.97912 17.85 6.85812C16.894 6.73512 15.634 6.73312 13.86 6.73312H10.14C8.366 6.73312 7.106 6.73512 6.15 6.85812Z"
                                                        fill="#12BF67" />
                                                    <path
                                                        d="M17.086 2.61012C16.226 2.50012 15.132 2.50012 13.767 2.50012H10.677C9.313 2.50012 8.218 2.50012 7.358 2.61012C6.468 2.72512 5.726 2.96812 5.137 3.53012C4.81194 3.84214 4.56405 4.22561 4.413 4.65012C4.917 4.42012 5.487 4.28412 6.127 4.20012C7.211 4.06012 8.597 4.06012 10.347 4.06012H14.261C16.011 4.06012 17.396 4.06012 18.481 4.20012C19.039 4.27312 19.545 4.38612 20 4.56612C19.8477 4.17472 19.6115 3.82144 19.308 3.53112C18.719 2.96812 17.977 2.72512 17.086 2.61112V2.61012Z"
                                                        fill="#12BF67" />
                                                </svg>

                                                <span
                                                    class="body2"><?php esc_html_e('Прикріпити', 'panterrea_v1'); ?></span>
                                            </button>
                                        </div>

                                        <div class="forum__composer__actions">
                                            <?php if (!$is_logged) : ?>
                                            <span class="forum__composer__guestHint caption">
                                                <?php
                                                echo wp_kses(
                                                    __('Публікація доступна <button type="button" class="forum__composer__guestHintLink js-forumOpenLoginPopup" aria-controls="forumLoginPopup">авторизованим</button> користувачам', 'panterrea_v1'),
                                                    [
                                                        'button' => [
                                                            'type' => true,
                                                            'class' => true,
                                                            'aria-controls' => true,
                                                        ],
                                                    ]
                                                );
                                                ?>
                                            </span>
                                            <?php endif; ?>
                                            <div class="form__editBtn hidden">
                                                <button type="button"
                                                    class="btn btn__transparent button-medium js-forumEditCancel">
                                                    <?php esc_html_e('Скасувати', 'panterrea_v1'); ?>
                                                </button>
                                                <button type="button"
                                                    class="btn btn__submit button-medium js-forumEdit">
                                                    <?php esc_html_e('Редагувати', 'panterrea_v1'); ?>
                                                </button>
                                            </div>
                                            <button class="btn btn__submit button-medium js-forumPublish" type="submit">
                                                <?php esc_html_e('Опублікувати', 'panterrea_v1'); ?>
                                            </button>
                                        </div>
                                    </div>

                                </form>
                            </div>
                        </div>

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
                        $args = [
                            'post_type' => 'post',
                            'posts_per_page' => 10,
                            'orderby' => 'date',
                            'order' => 'DESC',
                            'paged' => $forum_paged,
                            's' => $forum_search_query,
                            'meta_query' => [
                                [
                                    'key' => '_is_forum_post',
                                    'value' => '1',
                                    'compare' => '=',
                                ],
                            ],
                        ];
                        if ($forum_sort === 'mine' && is_user_logged_in()) {
                            $args['author'] = get_current_user_id();
                        }
                        $forum_tax_q = panterrea_forum_tax_query_for_categories($forum_cat_ids);
                        if ($forum_tax_q !== []) {
                            $args['tax_query'] = $forum_tax_q;
                        }

                        if ($forum_sort === 'popular') {
                            $GLOBALS['panterrea_forum_popular_order'] = true;
                        }

                        $query = new WP_Query($args);

                        unset($GLOBALS['panterrea_forum_popular_order']);

                        if ($query->have_posts()) : ?>
                    <div id="infiniteScrollForum"
                        class="forum__listPost <?php echo !is_user_logged_in() ? 'is-guest' : ''; ?>"
                        data-current-page="<?php echo esc_attr($forum_paged); ?>"
                        data-max-pages="<?php echo esc_attr($query->max_num_pages); ?>"
                        data-only-my="<?php echo esc_attr($forum_only_my ? '1' : '0'); ?>"
                        data-forum-sort="<?php echo esc_attr($forum_sort); ?>"
                        data-forum-cats="<?php echo esc_attr(implode(',', $forum_cat_ids)); ?>">
                        <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <?php get_template_part('template-parts/forum-item'); ?>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                    <?php if ($query->max_num_pages > 1) : ?>
                    <div class="forum__loadMore">
                        <button type="button" id="forumLoadMoreBtn" class="btn btn__loadMore button-medium">
                            <?php esc_html_e('Показати більше', 'panterrea_v1'); ?>
                        </button>
                    </div>
                    <?php endif; ?>
                    <?php else : ?>
                    <div id="infiniteScrollForum"
                        class="forum__listPost <?php echo !is_user_logged_in() ? 'is-guest' : ''; ?>"
                        data-current-page="<?php echo esc_attr($forum_paged); ?>" data-max-pages="0"
                        data-only-my="<?php echo esc_attr($forum_only_my ? '1' : '0'); ?>"
                        data-forum-sort="<?php echo esc_attr($forum_sort); ?>"
                        data-forum-cats="<?php echo esc_attr(implode(',', $forum_cat_ids)); ?>">
                        <div class="forum__itemPost">
                            <div class="forum__itemPost__content body2">
                                <?= __('Стрічка поки що пуста. Ви можете бути першим!', 'panterrea_v1') ?>
                            </div>
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

    <!-- ── Login popup (shown to guests on publish attempt) ── -->
    <?php if (!is_user_logged_in()) : ?>
    <div id="forumLoginPopup" class="forum__loginPopup" hidden aria-modal="true" role="dialog"
        aria-label="<?php esc_attr_e('Увійти до PanTerrea', 'panterrea_v1'); ?>">
        <div class="forum__loginPopup__overlay js-forumLoginPopupClose"></div>
        <div class="forum__loginPopup__inner">
            <button type="button" class="forum__loginPopup__close js-forumLoginPopupClose"
                aria-label="<?php esc_attr_e('Закрити', 'panterrea_v1'); ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>
            <?php get_template_part('template-parts/login-form', null, [
                'context'  => 'forum',
                'redirect' => URL_FORUM,
            ]); ?>
        </div>
    </div>
    <?php endif; ?>

</main>

<?php
get_footer();