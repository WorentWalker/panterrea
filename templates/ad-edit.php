<?php
/**
 * Template Name: Edit Ad
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

$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

$author_id = (int)get_post_field('post_author', $post_id);
$is_author = ($author_id === get_current_user_id());

if (!$is_author || $post_id === 0) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    include get_template_directory() . '/404.php';
    exit;
}

$terms = wp_get_post_terms($post_id, 'catalog_category');
$category_name = '';
$subcategory_name = '';

if (!empty($terms)) {
    $parent_term = $terms[0]->parent ? get_term($terms[0]->parent, 'catalog_category') : null;

    if ($parent_term) {
        $category_name = $parent_term->name;
        $subcategory_name = $terms[0]->name;
    } else {
        $category_name = $terms[0]->name;
    }
}

$tags = wp_get_post_terms($post_id, 'catalog_tag');

$selected_type = '';
$selected_condition = 'new';

if (!empty($tags)) {
    foreach ($tags as $tag) {
        if (in_array($tag->name, ['Продаж', 'Купівля', 'Оренда'])) {
            $selected_type = $tag->name;
        }
        if (in_array($tag->slug, ['new', 'used'])) {
            $selected_condition = $tag->slug;
        }
    }
}

$dataPost = get_field('catalog_post', $post_id);

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

get_header();

/*global $currentLang;*/

?>

    <main class="adCreate firstSectionPadding">

        <div class="container">

            <div class="adCreate__innerForm">
                <div class="adCreate__innerForm__title h4"><?= __('Редагування оголошення', 'panterrea_v1') ?></div>

                <div class="adCreate__form">
                    <div class="adCreate__form__title h6"><?= __('Опишіть ваше оголошення', 'panterrea_v1') ?></div>

                    <form id="formAdEdit" class="form form__adCreate" enctype="multipart/form-data" data-post-id="<?php echo esc_attr($post_id); ?>">

                        <div class="input__formAd">
                            <label class="subtitle2" for="adName"><?= __('Назвіть ваше оголошення', 'panterrea_v1') ?> <span class="required">*</span></label>
                            <input class="body2" type="text" name="adName" id="adName" placeholder="<?= __('Назва оголошення', 'panterrea_v1') ?>"
                                   value="<?php echo esc_attr(get_the_title($post_id)); ?>" data-symbolCount="100"/>
                            <div class="input__formAd__helperVal">
                                <span class="error caption"></span>
                                <span class="symbolCount caption">
                                    <span class="symbolCount__label"><?php _e('Використано символів', 'panterrea_v1'); ?></span>
                                    <span class="symbolCount__value">0/100</span>
                                </span>
                            </div>
                        </div>

                        <div class="input__formAd">
                            <label class="subtitle2" for="adRegion"><?= __('Укажіть регіон/місто', 'panterrea_v1') ?></label>
                            <input class="body2" type="text" name="adRegion" id="adRegion" placeholder="<?= __('Регіон оголошення', 'panterrea_v1') ?>"
                                   value="<?= esc_attr(get_the_content(null, false, $post_id)); ?>"/>
                            <div class="input__formAd__helperVal">
                                <span class="error caption"></span>
                            </div>
                        </div>

                        <div class="form__row">
                            <div class="input__formAd">

                                <?php
                                $subcategory_name_show = '';

                                if ($parent_term) {
                                    $category_name_show = $parent_term->name;
                                    $subcategory_name_show = $terms[0]->name;
                                } else {
                                    $category_name_show = $terms[0]->name;
                                }
                                ?>

                                <label class="subtitle2" for="adCategory"><?= __('Виберіть категорію', 'panterrea_v1') ?> <span class="required">*</span></label>

                                <div id="translated-category-names" style="display: none;">
                                    <div id="categoryFullName">
                                        <?php echo esc_html($subcategory_name_show ? "$category_name_show / $subcategory_name_show" : $category_name_show); ?>
                                    </div>
                                </div>

                                <input class="body2 js-openPopUp" type="text" name="adCategory" id="adCategory"
                                       placeholder="<?= __('Виберіть зі списку', 'panterrea_v1') ?>" readonly data-popUp="selectCategory"
                                       value="<?php echo esc_attr($subcategory_name_show ? "$category_name_show / $subcategory_name_show" : $category_name_show); ?>"/>
                                <div class="input__arrowBottom"></div>
                                <span class="error caption"></span>
                            </div>

                            <div class="input__formAd js-fakeSelectWrapper" data-show-inputs="category">
                                <label class="subtitle2" for="adType"><?= __('Виберіть тип оголошення', 'panterrea_v1') ?> <span class="required">*</span></label>
                                <input class="body2 js-fakeSelectInput" type="text" name="adType" id="adType"
                                       value="<?= __(esc_attr($selected_type), 'panterrea_v1') ?>" readonly/>

                                <ul class="input__fakeSelect js-fakeSelectOptions">
                                    <li class="body2" data-value="Продаж"><?= __('Продаж', 'panterrea_v1') ?></li>
                                    <li class="body2" data-value="Купівля"><?= __('Купівля', 'panterrea_v1') ?></li>
                                    <li class="body2" data-value="Оренда" data-show-inputs="machinery"><?= __('Оренда', 'panterrea_v1') ?></li>
                                </ul>

                                <div class="input__arrowBottom"></div>
                                <span class="error caption"></span>
                            </div>
                        </div>

                        <div class="form__row" data-show-inputs="category">
                            <div class="form__rowCurrency">

                                <div class="input__formAd">
                                    <label class="subtitle2" for="adPrice"><?= __('Вартість', 'panterrea_v1') ?> <span class="required">*</span></label>

                                    <?php
                                    $priceValue = $dataPost['price'];
                                    $priceValue = (is_numeric($priceValue) && floatval($priceValue) > 0) ? $priceValue : '';
                                    ?>

                                    <input class="body2" type="text" name="adPrice" id="adPrice" placeholder="0.00" value="<?php echo esc_attr($priceValue); ?>"/>
                                    <span class="error caption"></span>
                                </div>

                                <div class="input__formAd js-fakeSelectWrapper">
                                    <label class="subtitle2" for="adCurrency"><?= __('Валюта', 'panterrea_v1') ?> <span class="required">*</span></label>
                                    <input class="body2 js-fakeSelectInput" type="text" name="adCurrency" id="adCurrency" value="<?= __(esc_attr($dataPost['currency']), 'panterrea_v1') ?>" readonly/>

                                    <ul class="input__fakeSelect js-fakeSelectOptions">
                                        <li class="body2" data-value="грн"><?= __('грн', 'panterrea_v1') ?></li>
                                        <li class="body2" data-value="$">$</li>
                                    </ul>

                                    <div class="input__arrowBottom"></div>
                                    <span class="error caption"></span>
                                </div>

                            </div>

                            <div class="input__formAd" data-show-inputs="machinery">
                                <label class="subtitle2" for="adCondition"><?= __('Стан', 'panterrea_v1') ?> <span class="required">*</span></label>

                                <div class="input__radioGroup">
                                    <div class="input__radioGroup__radio">
                                        <input type="radio" name="adCondition" id="new" value="new" <?php echo ($selected_condition === 'new') ? 'checked' : ''; ?>>
                                        <label class="button-large" for="new"><span><?= __('Новий', 'panterrea_v1') ?></span></label>
                                    </div>
                                    <div class="input__radioGroup__radio">
                                        <input type="radio" name="adCondition" id="used" value="used" <?php echo ($selected_condition === 'used') ? 'checked' : ''; ?>>
                                        <label class="button-large" for="used"><span><?= __('Вживаний', 'panterrea_v1') ?></span></label>
                                    </div>
                                </div>

                                <span class="error caption"></span>
                            </div>

                        </div>

                        <div class="input__formAd">
                            <label class="subtitle2" for="adDesc"><?= __('Опис', 'panterrea_v1') ?> <span class="required">*</span></label>
                            <textarea class="body2" name="adDesc" id="adDesc"
                                      placeholder="<?= __('Напишіть щось круте...', 'panterrea_v1') ?>" data-symbolCount="1000"><?= wp_strip_all_tags($dataPost['info']); ?></textarea>
                            <div class="input__formAd__helperVal">
                                <span class="error caption"></span>
                                <span class="symbolCount caption">
                                    <span class="symbolCount__label"><?php _e('Використано символів', 'panterrea_v1'); ?></span>
                                    <span class="symbolCount__value">0/1000</span>
                                </span>
                            </div>
                        </div>

                        <div class="input__formAd">
                            <label class="subtitle2" for="adPhoto"><?= __('Фото', 'panterrea_v1') ?> <span class="info body2"><?= __('(Підтримуються файли до 5 МБ у форматі JPEG, PNG.)', 'panterrea_v1') ?></span></label>

                            <div id="adPhoto" class="input__imageUpload">
                                <div id="previewContainer" class="input__previewContainer">

                                    <script>
                                        const initialImages = <?php echo json_encode(array_map(function ($image) {
                                            return [
                                                'name' => basename($image['url']),
                                                'preview' => $image['url'],
                                                'existing' => true,
                                                'file' => null
                                            ];
                                        }, $images)); ?>;
                                    </script>

                                </div>
                            </div>

                            <span class="error caption"></span>
                        </div>

                        <div class="form__rowBtn">
                            <a href="<?php echo esc_attr(get_permalink($post_id)); ?>" class="btn btn__submit btn__submit__transparentGreen button-large">
                                <?= __('Скасувати', 'panterrea_v1') ?>
                            </a>
                            <button class="btn btn__submit button-large" type="submit"><?= __('Опублікувати зміни', 'panterrea_v1') ?></button>
                        </div>

                        <div id="selectCategory" class="popUp js-popUp hidden">
                            <div class="container">
                                <div class="popUp__inner">
                                    <div class="popUp__selectCategory">
                                        <div class="popUp__title h6"><?= __('Виберіть категорію', 'panterrea_v1') ?></div>

                                        <div class="popUp__slider">
                                            <div id="categorySelectSlider" class="popUp__categories">
                                                <?php
                                                $args = array(
                                                    'taxonomy' => 'catalog_category',
                                                    'hide_empty' => false,
                                                    'meta_key' => 'category_order',
                                                    'orderby' => 'meta_value_num',
                                                    'order' => 'ASC',
                                                    'parent' => 0
                                                );

                                                $categories = get_terms($args);

                                                if ($categories && !is_wp_error($categories)) {
                                                    foreach ($categories as $category) {
                                                        $category_image = get_field('category_image', 'term_' . $category->term_id);
                                                        $category_link = get_term_link($category);

                                                        $is_active = '';
                                                        if ($category_name == $category->name) {
                                                            $is_active = 'active';
                                                        }
                                                        /*$category_name_show = ($currentLang === 'en' && ($name_en = get_field('name_en', $category))) ? $name_en : $category->name;*/
                                                        $category_name_show = $category->name;
                                                        ?>
                                                        <div class="popUp__category <?php echo esc_attr($is_active); ?>" data-id="<?php echo esc_attr($category->term_id); ?>">
                                                            <?php
                                                            if ($category_image) {
                                                                echo '<img src="' . esc_url($category_image['url']) . '" alt="' . esc_attr($category_name_show) . '">';
                                                            }
                                                            ?>
                                                            <div class="popUp__category__title subtitle2"><?php echo esc_html($category_name_show); ?></div>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>

                                            <?php if (count($categories) > 7) { ?>
                                                <div id="categorySelectSliderPrev" class="popUp__slider__prev disabled">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                                                        <path d="M11.5247 15.8333C11.2726 15.8342 11.0336 15.7208 10.8747 15.525L6.84973 10.525C6.59697 10.2175 6.59697 9.77415 6.84973 9.46665L11.0164 4.46665C11.3109 4.11227 11.837 4.06377 12.1914 4.35832C12.5458 4.65287 12.5943 5.17893 12.2997 5.53332L8.57473 9.99998L12.1747 14.4667C12.3828 14.7164 12.4266 15.0644 12.287 15.358C12.1474 15.6515 11.8498 15.8371 11.5247 15.8333Z"/>
                                                    </svg>
                                                </div>
                                                <div id="categorySelectSliderNext" class="popUp__slider__next">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
                                                        <path d="M8.47722 4.16668C8.72938 4.16582 8.96837 4.27919 9.12722 4.47501L13.1522 9.47501C13.405 9.78252 13.405 10.2258 13.1522 10.5333L8.98556 15.5333C8.69101 15.8877 8.16494 15.9362 7.81056 15.6417C7.45617 15.3471 7.40767 14.8211 7.70222 14.4667L11.4272 10L7.82722 5.53335C7.61915 5.28358 7.57531 4.93561 7.71494 4.64204C7.85456 4.34846 8.15216 4.16288 8.47722 4.16668Z"/>
                                                    </svg>
                                                </div>
                                            <?php } ?>
                                        </div>

                                        <div class="popUp__subCategories">
                                            <?php
                                            if (!empty($category_name)) {
                                                $active_category = get_terms(array(
                                                    'taxonomy' => 'catalog_category',
                                                    'hide_empty' => false,
                                                    'name' => $category_name,
                                                ));

                                                if (!empty($active_category) && !is_wp_error($active_category)) {
                                                    $active_category = $active_category[0];

                                                    $subcategories = get_terms(array(
                                                        'taxonomy' => 'catalog_category',
                                                        'hide_empty' => false,
                                                        'parent' => $active_category->term_id,
                                                    ));

                                                    if ($subcategories && !is_wp_error($subcategories)) {
                                                        foreach ($subcategories as $subcategory) {
                                                            ?>
                                                            <div class="popUp__subcategory h6 <?php echo esc_attr($subcategory_name == $subcategory->name ? 'active' : ''); ?>"
                                                                 data-id="<?php echo esc_attr($subcategory->term_id); ?>">
                                                                <?php
                                                                /*$subcategory_name_show = ($currentLang === 'en' && ($name_en = get_field('name_en', $subcategory))) ? $name_en : $subcategory->name;*/
                                                                $subcategory_name_show = $subcategory->name;
                                                                echo esc_html($subcategory_name_show);
                                                                ?>
                                                            </div>
                                                            <?php
                                                        }
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>

                                        <?php
                                        $subcategories = get_terms(array(
                                            'taxonomy' => 'catalog_category',
                                            'hide_empty' => false
                                        ));
                                        ?>

                                        <div id="translatedSubcategories" style="display: none;">
                                            <?php foreach ($subcategories as $subcategory): ?>
                                                <div class="subcategory-label" data-id="<?= esc_attr($subcategory->term_id); ?>">
                                                    <?= esc_html($subcategory->name); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="popUp__rowBtn">
                                            <div id="cancelSelectCategory"
                                                 class="btn btn__submit btn__submit__transparentGreen button-large">
                                                <?php _e('Відміна', 'panterrea_v1'); ?>
                                            </div>
                                            <div id="applySelectCategory" class="btn btn__submit button-large">
                                                <?php _e('Вибрати', 'panterrea_v1'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                </div>

            </div>

        </div>

    </main>

<?php
get_footer();
