<?php
/**
 * Template Name: Create an Ad
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!is_user_logged_in()) {
    wp_redirect(URL_LOGIN);
    exit;
}

/*global $currentLang;*/

$user_id = get_current_user_id();
$email_confirmed = get_user_meta($user_id, 'email_verified', true);

if (!$email_confirmed) {
    setMessageCookies('warning', __('Підтвердіть свою електронну пошту для доступу.', 'panterrea_v1'));
    wp_redirect(home_url());
    exit;
}

$author_name = trim(get_user_meta($user_id, 'name', true));

get_header();
?>

<main class="adCreate firstSectionPadding">

    <div class="container">

        <div id="stepInfo" class="adCreate__innerForm">
            <?php catalog_breadcrumbs(); ?>
            <div class="adCreate__innerForm__title h4"><?php _e('Створення оголошення', 'panterrea_v1'); ?></div>

            <div class="adCreate__form">
                <div class="adCreate__form__title h6"><?php _e('Опишіть ваше оголошення', 'panterrea_v1'); ?></div>

                <form id="formAdCreate" class="form form__adCreate" enctype="multipart/form-data">

                    <div class="input__formAd">
                        <label class="subtitle2" for="adName"><?php _e('Назвіть ваше оголошення', 'panterrea_v1'); ?>
                            <span class="required">*</span></label>
                        <input class="body2" type="text" inputmode="text" name="adName" id="adName"
                            placeholder="<?php _e('Назва оголошення', 'panterrea_v1'); ?>" data-symbolCount="100"
                            autocomplete="off" />
                        <div class="input__formAd__helperVal">
                            <span class="error caption"></span>
                            <span class="symbolCount caption">
                                <span
                                    class="symbolCount__label"><?php _e('Використано символів', 'panterrea_v1'); ?></span>
                                <span class="symbolCount__value">0/100</span>
                            </span>
                        </div>
                    </div>

                    <div class="input__formAd">
                        <label class="subtitle2"
                            for="adRegion"><?php _e('Укажіть регіон/місто', 'panterrea_v1'); ?></label>
                        <input class="body2" type="text" name="adRegion" id="adRegion"
                            placeholder="<?php _e('Регіон оголошення', 'panterrea_v1'); ?>" />
                        <div class="input__formAd__helperVal">
                            <span class="error caption"></span>
                        </div>
                    </div>

                    <div class="form__row">
                        <div class="input__formAd">
                            <label class="subtitle2" for="adCategory"><?php _e('Виберіть категорію', 'panterrea_v1'); ?>
                                <span class="required">*</span></label>
                            <input class="body2 js-openPopUp" type="text" name="adCategory" id="adCategory"
                                placeholder="<?php _e('Виберіть зі списку', 'panterrea_v1'); ?>" readonly
                                data-popUp="selectCategory" />
                            <div class="input__arrowBottom"></div>
                            <span class="error caption"></span>
                        </div>

                        <div class="input__formAd js-fakeSelectWrapper" data-show-inputs="category">
                            <label class="subtitle2"
                                for="adType"><?php _e('Виберіть тип оголошення', 'panterrea_v1'); ?> <span
                                    class="required">*</span></label>
                            <input class="body2 js-fakeSelectInput" type="text" name="adType" id="adType"
                                value="<?php _e('Продаж', 'panterrea_v1'); ?>" readonly />

                            <ul class="input__fakeSelect js-fakeSelectOptions">
                                <li class="body2" data-value="Продаж"><?php _e('Продаж', 'panterrea_v1'); ?></li>
                                <li class="body2" data-value="Купівля"><?php _e('Купівля', 'panterrea_v1'); ?></li>
                                <li class="body2" data-value="Оренда" data-show-inputs="machinery">
                                    <?php _e('Оренда', 'panterrea_v1'); ?></li>
                            </ul>

                            <div class="input__arrowBottom"></div>
                            <span class="error caption"></span>
                        </div>
                    </div>

                    <div class="form__row" data-show-inputs="category">
                        <div class="form__rowCurrency">

                            <div class="input__formAd">
                                <label class="subtitle2" for="adPrice"><?php _e('Вартість', 'panterrea_v1'); ?> <span
                                        class="required">*</span></label>
                                <input class="body2" type="text" name="adPrice" id="adPrice" placeholder="0.00" />
                                <span class="error caption"></span>
                            </div>

                            <div class="input__formAd js-fakeSelectWrapper">
                                <label class="subtitle2" for="adCurrency"><?php _e('Валюта', 'panterrea_v1'); ?> <span
                                        class="required">*</span></label>
                                <input class="body2 js-fakeSelectInput" type="text" name="adCurrency" id="adCurrency"
                                    value="<?php _e('грн', 'panterrea_v1'); ?>" readonly />

                                <ul class="input__fakeSelect js-fakeSelectOptions">
                                    <li class="body2" data-value="грн"><?php _e('грн', 'panterrea_v1'); ?></li>
                                    <li class="body2" data-value="$"><?php _e('$', 'panterrea_v1'); ?></li>
                                </ul>

                                <div class="input__arrowBottom"></div>
                                <span class="error caption"></span>
                            </div>

                        </div>

                        <div class="input__formAd" data-show-inputs="machinery">
                            <label class="subtitle2" for="adCondition"><?php _e('Стан', 'panterrea_v1'); ?> <span
                                    class="required">*</span></label>

                            <div class="input__radioGroup">
                                <div class="input__radioGroup__radio">
                                    <input type="radio" name="adCondition" id="new" value="new" checked>
                                    <label class="button-large"
                                        for="new"><span><?php _e('Новий', 'panterrea_v1'); ?></span></label>
                                </div>
                                <div class="input__radioGroup__radio">
                                    <input type="radio" name="adCondition" id="used" value="used">
                                    <label class="button-large"
                                        for="used"><span><?php _e('Вживаний', 'panterrea_v1'); ?></span></label>
                                </div>
                            </div>

                            <span class="error caption"></span>
                        </div>

                    </div>

                    <div class="input__formAd">
                        <label class="subtitle2" for="adDesc"><?php _e('Опис', 'panterrea_v1'); ?><span
                                class="required">*</span></label>
                        <textarea class="body2" name="adDesc" id="adDesc"
                            placeholder="<?php _e('Вкажіть породу, вік, кількість, стан...', 'panterrea_v1'); ?>"
                            data-symbolCount="2000"></textarea>
                        <div class="input__formAd__helperVal">
                            <span class="error caption"></span>
                            <span class="symbolCount caption">
                                <span
                                    class="symbolCount__label"><?php _e('Використано символів', 'panterrea_v1'); ?></span>
                                <span class="symbolCount__value">0/2000</span>
                            </span>
                        </div>
                    </div>

                    <div class="input__formAd">
                        <label class="subtitle2" for="adPhoto"><?php _e('Фото', 'panterrea_v1'); ?>
                            <span
                                class="info body2"><?php _e('(Підтримується файли до 5 МБ у форматі JPEG, PNG.)', 'panterrea_v1'); ?></span>
                        </label>

                        <div id="adPhoto" class="input__imageUpload">
                            <div id="previewContainer" class="input__previewContainer">

                            </div>
                        </div>

                        <span class="error caption"></span>
                    </div>

                    <div class="form__rowBtn">
                        <div class="btn btn__submit btn__submit__transparentGreen button-large js-reload">
                            <?php _e('Скасувати', 'panterrea_v1'); ?>
                        </div>
                        <button class="btn btn__submit button-large" type="submit">
                            <?php _e('Опублікувати', 'panterrea_v1'); ?>
                        </button>
                    </div>

                    <div id="selectCategory" class="popUp js-popUp hidden">
                        <div class="container">
                            <div class="popUp__inner">
                                <div class="popUp__selectCategory">
                                    <div class="popUp__title h6">
                                        <?php _e('Виберіть категорію', 'panterrea_v1'); ?>
                                    </div>

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

                                                        $category_name = $category->name;
                                                        /*$category_name = ($currentLang === 'en' && ($name_en = get_field('name_en', $category))) ? $name_en : $category->name;*/
                                                        ?>
                                            <div class="popUp__category"
                                                data-id="<?php echo esc_attr($category->term_id); ?>">
                                                <?php
                                                            if ($category_image) {
                                                                echo '<img src="' . esc_url($category_image['url']) . '" alt="' . esc_attr($category_name) . '">';
                                                            }
                                                            ?>
                                                <div class="popUp__category__title subtitle2">
                                                    <?php echo esc_html($category_name); ?></div>
                                            </div>
                                            <?php
                                                    }
                                                } ?>
                                        </div>

                                        <?php if (count($categories) > 7) { ?>
                                        <div id="categorySelectSliderPrev" class="popUp__slider__prev disabled">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M11.5247 15.8333C11.2726 15.8342 11.0336 15.7208 10.8747 15.525L6.84973 10.525C6.59697 10.2175 6.59697 9.77415 6.84973 9.46665L11.0164 4.46665C11.3109 4.11227 11.837 4.06377 12.1914 4.35832C12.5458 4.65287 12.5943 5.17893 12.2997 5.53332L8.57473 9.99998L12.1747 14.4667C12.3828 14.7164 12.4266 15.0644 12.287 15.358C12.1474 15.6515 11.8498 15.8371 11.5247 15.8333Z" />
                                            </svg>
                                        </div>
                                        <div id="categorySelectSliderNext" class="popUp__slider__next">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M8.47722 4.16668C8.72938 4.16582 8.96837 4.27919 9.12722 4.47501L13.1522 9.47501C13.405 9.78252 13.405 10.2258 13.1522 10.5333L8.98556 15.5333C8.69101 15.8877 8.16494 15.9362 7.81056 15.6417C7.45617 15.3471 7.40767 14.8211 7.70222 14.4667L11.4272 10L7.82722 5.53335C7.61915 5.28358 7.57531 4.93561 7.71494 4.64204C7.85456 4.34846 8.15216 4.16288 8.47722 4.16668Z" />
                                            </svg>
                                        </div>
                                        <?php } ?>
                                    </div>


                                    <div class="popUp__subCategories"></div>

                                    <?php
                                        $subcategories = get_terms(array(
                                            'taxonomy' => 'catalog_category',
                                            'hide_empty' => false
                                        ));
                                        ?>

                                    <div id="translatedSubcategories" style="display: none;">
                                        <?php foreach ($subcategories as $subcategory): ?>
                                        <div class="subcategory-label"
                                            data-id="<?= esc_attr($subcategory->term_id); ?>">
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

        <div id="stepType" class="adCreate__innerType hidden">
            <div class="adCreate__title h3">
                <?php _e('Рекламуй та швидко продавай', 'panterrea_v1'); ?>
            </div>
            <div class="adCreate__subtitle body1">
                <?php _e('Вибери спосіб реклами який тобі найбільше підходить', 'panterrea_v1'); ?>
            </div>

            <div class="adCreate__contentType">

                <div class="adCreate__contentType__item js-adType active" data-adType="boost">
                    <div class="title h4">
                        <?php _e('Швидко продаю', 'panterrea_v1'); ?>
                        <span class="tag label-text"><?php _e('Найпопулярніший', 'panterrea_v1'); ?></span>
                    </div>
                    <div class="price">
                        <?php echo esc_html(__('90,00 грн', 'panterrea_v1')); ?>
                    </div>
                    <div class="sep"></div>
                    <div class="list">
                        <div class="list__title overline"><?php _e('Переваги', 'panterrea_v1'); ?></div>
                        <div class="list__item body2 checked">
                            <?php _e('Підняття в топ протягом 30ти днів, що в рази пришвидшить продаж', 'panterrea_v1'); ?>
                        </div>
                    </div>
                </div>

                <div class="adCreate__contentType__item js-adType" data-adType="free">
                    <div class="title h4"><?php _e('Безкоштовно', 'panterrea_v1'); ?></div>
                    <div class="price">
                        <?php echo esc_html(__('0,00 грн', 'panterrea_v1')); ?>
                    </div>
                    <div class="sep"></div>
                    <div class="list">
                        <div class="list__title overline"><?php _e('Переваги', 'panterrea_v1'); ?></div>
                        <div class="list__item body2">
                            <?php _e('Розміщення оголошення на платформі та долучення до найбільшого в Україні агро комюніті. Ми раді допомогти вам розвивати вашу справу)', 'panterrea_v1'); ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="adCreate__rowBtn">
                <div class="btn btn__submit btn__submit__transparentGreen button-large js-backToForm">
                    <?php _e('Назад', 'panterrea_v1'); ?>
                </div>
                <div id="adCreateButton" class="btn btn__submit button-large">
                    <?php _e('Продовжити', 'panterrea_v1'); ?>
                </div>
            </div>
        </div>

        <div id="stepPayment" class="adCreate__innerPayment hidden">
            <div class="adCreate__title h3">
                <?php _e('Давайте завершимо!', 'panterrea_v1'); ?>
            </div>
            <div class="adCreate__subtitle body1">
                <?php _e('Це найкращий вибір для Вас', 'panterrea_v1'); ?>
            </div>

            <form id="formBoost" class="form__boost" data-post-id=""
                data-author-name="<?php echo esc_attr($author_name); ?>">

                <div class="form__boost__left">
                    <div class="form__boost__left__title h6">
                        <?php _e('Платіжні дані', 'panterrea_v1'); ?>
                    </div>
                    <div id="card-element" class="form__boost__left__card">
                        <!-- Stripe Elements -->
                    </div>
                </div>

                <div class="form__boost__right">
                    <div class="title h4"><?php _e('Швидкий', 'panterrea_v1'); ?> <span
                            class="tag label-text"><?php _e('Найпопулярніший', 'panterrea_v1'); ?></span></div>
                    <div class="price h2"><?php _e('90,00 грн', 'panterrea_v1'); ?></div>
                    <div class="sep"></div>
                    <div class="list">
                        <div class="list__title overline"><?php _e('Переваги', 'panterrea_v1'); ?></div>
                        <div class="list__item body2 checked">
                            <span><?php _e('Підняття в топ протягом 30ти днів, що в рази пришвидшить продаж', 'panterrea_v1'); ?></span>
                        </div>
                    </div>
                    <div class="totalPrice subtitle1"><?php _e('Загальна сума оплати', 'panterrea_v1'); ?><span
                            class="subtitle1"><?php _e('90,00 грн', 'panterrea_v1'); ?></span></div>
                    <button id="submit"
                        class="btn btn__submit button-large"><?php _e('Оплатити рекламу', 'panterrea_v1'); ?></button>
                    <div class="btn btn__submit btn__submit__transparentGreen button-large js-goSuccess">
                        <?php _e('Пропустити оплату', 'panterrea_v1'); ?></div>
                </div>

            </form>
        </div>

        <div id="stepSuccess" class="adCreate__innerSuccess hidden">
            <div class="adCreate__title h3"><?php _e('Ваше оголошення відправлено на модерацію', 'panterrea_v1'); ?></div>
            <div class="adCreate__subtitle body1">
                <?php _e('Після схвалення вашого оголошення адміністратором ви отримаєте повідомлення. Оголошення з\'явиться в розділі "Мої оголошення"', 'panterrea_v1'); ?>
            </div>

            <img src="<?php echo esc_url(get_template_directory_uri() . '/src/img/ad_create.avif'); ?>" alt="Ad Create">

            <a href="<?= home_url() ?>"
                class="btn btn__submit button-large"><?php _e('Повернутись на Головну сторінку', 'panterrea_v1'); ?></a>
            <a href="<?= esc_url(URL_USERAD) ?>"
                class="btn btn__submit btn__submit__transparentGreen button-large"><?php _e('Переглянути Мої оголошення', 'panterrea_v1'); ?></a>
        </div>
    </div>

</main>

<?php
get_footer();