<?php

/**
 * Catalog Post Template
 */

get_header();

$post_id = get_the_ID();
$dataPost = get_field('catalog_post');
$dataPost['region'] = get_the_content(null, false, $post_id);
$author_id = (int)get_post_field('post_author', $post_id);

$post_title = get_the_title($post_id);
$featured_image_url = $dataPost['featured_image'] ?? '';
$featured_image_url = !empty($featured_image_url) ? $featured_image_url : get_the_post_thumbnail_url($post_id, 'medium');

$gallery = $dataPost['gallery'] ?? [];
$images = [];

if (!empty($gallery) && is_array($gallery)) {
    foreach ($gallery as $item) {
        if (!empty($item['image'])) {
            $images[] = ['url' => esc_url($item['image'])];
        }
    }
}

if (!empty($featured_image_url)) {
    array_unshift($images, ['url' => esc_url($featured_image_url)]);
}

$favorites = [];
if (is_user_logged_in()) {
    $favorites = get_user_meta(get_current_user_id(), 'favorite_posts', true);
    if (!$favorites) {
        $favorites = [];
    }
}

$current_user_id = get_current_user_id();

$is_author = ($author_id === get_current_user_id());
$is_active = get_post_meta($post_id, '_is_active', true);

if (!$is_active && !$is_author) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    include get_template_directory() . '/404.php';
    exit;
}

$is_boosted = get_post_meta($post_id, '_is_boosted', true);
?>

<main class="catalogPost firstSectionPadding <?php echo $is_author ? 'isAuthor' : ''; ?>">
    <div class="container">
        <div class="catalogPost__inner">

            <?php if (!$is_author) :
                    get_template_part('template-parts/search-input', null, ['width' => 'full']);
                endif; ?>

            <?php catalog_breadcrumbs(); ?>

            <?php if ($author_id && !$is_author) :
                    get_template_part('template-parts/author-info', null, ['author_id' => $author_id]);
                endif; ?>

            <div class="catalogPost__content">
                <div class="catalogPost__slider">
                    <?php if ($images) { ?>
                    <div id="catalogPostSlider" class="catalogPost__slider__inner">
                        <?php foreach ($images as $image) {
                                    $image_url = $image['url']; ?>
                        <div><img src="<?= esc_html($image_url); ?>" class="js-openPopUp-img js-openPopUp"
                                data-popUp="imagePopup" alt="<?= esc_attr($post_title); ?>"
                                data-full="<?= esc_url($image_url); ?>" />
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                    <?php if (count($images) > 1) { ?>
                    <div class="catalogPost__sliderNav">
                        <div id="catalogPostSliderPrev" class="catalogPost__sliderNav__prev"></div>
                        <span id="catalogPostSliderCounter"
                            class="catalogPost__sliderNav__counter subtitle2">1/<?= count($images); ?></span>
                        <div id="catalogPostSliderNext" class="catalogPost__sliderNav__next"></div>
                    </div>
                    <?php } ?>
                </div>

                <div class="catalogPost__info">
                    <h1 class="catalogPost__info__title h4"><?= esc_attr($post_title); ?></h1>
                    <div class="catalogPost__info__row">
                        <h2 class="catalogPost__info__price h5">
                            <?php
                                $price = $dataPost['price'] ?? null;
                                $currency = $dataPost['currency'] ?? '';

                                if (!is_numeric($price) || floatval($price) <= 0) {
                                    esc_html_e('Ціна договірна', 'panterrea_v1');
                                } else {
                                    echo esc_html($price) . ' ' . esc_html__($currency, 'panterrea_v1');
                                }
                                ?>
                        </h2>
                        <?php
                            if (!$is_boosted && $is_active && $is_author) { ?>
                        <a href="<?php echo esc_url(URL_BOOSTAD . '?post_id=' . $post_id); ?>"
                            class="btn btn__boost button-medium"><?php echo __('Рекламувати', 'panterrea_v1'); ?></a>
                        <?php } ?>
                    </div>
                    <?php if (!empty($dataPost['region'])) : ?>
                    <h3 class="catalogPost__info__region h6">
                        <?= wp_kses_post($dataPost['region']); ?>
                    </h3>
                    <?php endif; ?>
                    <p class="catalogPost__info__text body2"><?= wp_kses_post($dataPost['info']); ?></p>

                    <?php if ($is_author) { ?>

                    <div class="catalogPost__edits">

                        <div class="catalogPost__deactivate">
                            <span class="table-head"><?php echo __('Тимчасово деактивувати', 'panterrea_v1'); ?></span>
                            <div id="statusAd"
                                class="switcher js-toggleSwitch <?php echo esc_attr($is_active === '1' ? '' : 'active'); ?>">
                            </div>
                        </div>

                        <a href="<?php echo esc_url(URL_EDITAD . '?post_id=' . $post_id); ?>"
                            class="btn btn__editAd button-large catalogPost__edits__edit"><?php echo __('Редагувати', 'panterrea_v1'); ?></a>

                        <div class="btn btn__deleteAd button-large catalogPost__edits__delete js-openPopUp"
                            data-popUp="deleteAd"><?php echo __('Видалити', 'panterrea_v1'); ?>
                        </div>

                        <div id="deleteAd" class="popUp js-popUp hidden">
                            <div class="container">
                                <div class="popUp__inner confirm">
                                    <div class="popUp__confirm">

                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/src/svg/icon_delete_ad.svg'); ?>"
                                            alt="Empty">

                                        <div class="popUp__confirm__title h3">
                                            <?php echo __('Ви дійсно хочете видалити оголошення?', 'panterrea_v1'); ?>
                                        </div>
                                        <div class="popUp__confirm__text body2">
                                            <?php echo __('Оголошення буде видалено назавжди', 'panterrea_v1'); ?></div>

                                        <div id="deleteAdButton" class="btn btn__submit button-large">
                                            <?php echo __('Так, видалити', 'panterrea_v1'); ?></div>
                                        <div class="btn btn__cancelConfirm subtitle2 js-closePopUp"
                                            data-popUp="deleteAd"><?php echo __('Скасувати', 'panterrea_v1'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <?php } else { ?>

                    <div id="catalogPostButtons" class="catalogPost__buttons">

                        <div class="catalogPost__chat js-chatContainer hidden">
                            <?php get_template_part('template-parts/chat'); ?>
                        </div>

                        <div class="btn btn__submit button-large catalogPost__buttons__send js-chatOpen"
                            data-user-id="<?php echo esc_attr($current_user_id ?: 0); ?>"
                            data-recipient-id="<?php echo esc_attr($author_id); ?>"
                            data-post-id="<?php echo $post_id; ?>">
                            <?php echo __('Надіслати повідомлення', 'panterrea_v1'); ?>
                        </div>

                        <?php
                                $is_favorite = in_array(get_the_ID(), $favorites);
                                $class = $is_favorite ? 'active' : '';
                                ?>

                        <div class="btn btn__favorites button-large catalogPost__buttons__favorites js-favorites <?php echo esc_attr($class); ?>"
                            data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
                            <span><?php echo __('Додати в обране', 'panterrea_v1'); ?></span>
                        </div>

                        <div class="btn btn__share button-large catalogPost__buttons__share js-share">
                            <span><?php echo __('Поділитись', 'panterrea_v1'); ?></span>
                        </div>
                    </div>
                    <?php } ?>

                </div>
            </div>

            <?php
                if (!$is_author) :
                    $author_args = array(
                        'post__not_in' => array($post_id),
                        'post_type' => 'catalog_post',
                        'posts_per_page' => 4,
                        'author' => $author_id,
                        'meta_query' => [
                            [
                                'key' => '_is_active',
                                'value' => '1',
                                'compare' => '='
                            ]
                        ],
                        'orderby' => [
                            'meta_value_num' => 'DESC',
                            'date' => 'DESC'
                        ],
                        'meta_key' => '_is_boosted'
                    );

                    $author_query = new WP_Query($author_args);
                    $total_author_posts = $author_query->found_posts;

                    if ($author_query->have_posts()) : ?>

            <div class="catalogPost__subContent">
                <div class="catalogPost__subContent__nav">
                    <h3 class="catalogPost__subContent__title h3">
                        <?php $author_info = get_catalog_post_author_info($author_id); ?>
                        <?php printf(__('Інші оголошення %s (%s)', 'panterrea_v1'), esc_html($author_info['name'] ?? 'автора'), esc_html($total_author_posts)); ?>
                    </h3>
                    <a href="<?php echo esc_url(add_query_arg('author_id', $author_id, URL_AUTHORAD)); ?>"
                        class="btn btn__showAll button-large"><?php echo __('Показати всі', 'panterrea_v1'); ?></a>
                </div>

                <div class="catalogPost__subContent__posts">

                    <?php while ($author_query->have_posts()) : $author_query->the_post();
                                    get_template_part('template-parts/catalog-item', null, [
                                        'favorites' => $favorites
                                    ]);
                                endwhile;
                                wp_reset_postdata(); ?>

                </div>
            </div>

            <?php endif; ?>

            <?php

                    $categories = get_the_terms($post_id, 'catalog_category');
                    $category_ids = wp_list_pluck($categories, 'term_id');

                    $similar_args = array(
                        'post__not_in' => array($post_id),
                        'post_type' => 'catalog_post',
                        'posts_per_page' => 4,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'catalog_category',
                                'field' => 'term_id',
                                'terms' => $category_ids,
                            ),
                        ),
                        'meta_query' => [
                            [
                                'key' => '_is_active',
                                'value' => '1',
                                'compare' => '='
                            ]
                        ],
                        'orderby' => [
                            'meta_value_num' => 'DESC',
                            'date' => 'DESC'
                        ],
                        'meta_key' => '_is_boosted'
                    );

                    $similar_query = new WP_Query($similar_args);
                    $total_similar_posts = $similar_query->found_posts;

                    $term = get_term($category_ids[0], 'catalog_category');
                    $link = '#';
                    if ($term && !is_wp_error($term)) {
                        $link = panterrea_get_catalog_category_link($term);
                    }

                    if ($similar_query->have_posts()) : ?>

            <div class="catalogPost__subContent">
                <div class="catalogPost__subContent__nav">
                    <h3 class="catalogPost__subContent__title h3">
                        <?php printf(__('Подібні оголошення (%s)', 'panterrea_v1'), esc_html($total_similar_posts)); ?>
                    </h3>
                    <a href="<?= esc_url($link); ?>"
                        class="btn btn__showAll button-large"><?php echo __('Показати всі', 'panterrea_v1'); ?></a>
                </div>

                <div class="catalogPost__subContent__posts">

                    <?php while ($similar_query->have_posts()) : $similar_query->the_post();
                                    get_template_part('template-parts/catalog-item', null, [
                                        'favorites' => $favorites
                                    ]);
                                endwhile;
                                wp_reset_postdata(); ?>

                </div>
            </div>

            <?php endif;
                endif; ?>

        </div>
    </div>

    <div id="imagePopup" class="popUp js-popUp hidden">
        <div class="popUp__inner popUp__inner-noPadding">
            <div class="popUp__imagePopup">
                <div class="popUp__imageWrapper">
                    <img id="popupImage" src="" alt="Full" />
                </div>
            </div>
        </div>
    </div>

</main>


<?php

get_footer();